<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads;

use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;
use Backpack\CRUD\app\Library\CrudPanel\CrudField;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\CrudPanel\Uploads\Interfaces\UploaderInterface;
use Exception;

final class RegisterUploadEvents
{
    private string $crudObjectType;

    public function __construct(
        private readonly CrudField|CrudColumn $crudObject,
        private readonly array $uploaderConfiguration,
        private readonly string $macro
        ) {
        $this->crudObjectType = is_a($crudObject, CrudField::class) ? 'field' : (is_a($crudObject, CrudColumn::class) ? 'column' : null);

        if (! $this->crudObjectType) {
            abort(500, 'Upload handlers only work for CrudField and CrudColumn classes.');
        }
    }

    /**
     * From the given crud object and upload definition provide the event registry
     * service so that uploads are stored and retrieved automatically.
     *
     * @param  CrudField|CrudColumn  $crudObject
     * @param  array  $uploaderConfiguration
     * @param  string  $macro
     * @param  array|null  $subfield
     * @return void
     */
    public static function handle($crudObject, $uploaderConfiguration, $macro, $subfield = null): void
    {
        $instance = new self($crudObject, $uploaderConfiguration, $macro);

        $instance->registerEvents($subfield);
    }

    /**
     * Register the saving, retrieved and deleting events on model to handle the various upload processes.
     * In case of CrudColumn we only register the retrieved event.
     *
     * @param  string  $model
     * @param  UploaderInterface  $uploader
     * @return void
     */
    private function setupModelEvents(string $model, UploaderInterface $uploader): void
    {
        if (app('UploadersRepository')->isUploadHandled($uploader->getIdentifier())) {
            return;
        }

        if ($this->crudObjectType === 'field') {
            $model::saving(function ($entry) use ($uploader) {
                $updatedCountKey = 'uploaded_'.($uploader->getRepeatableContainerName() ?? $uploader->getName()).'_count';

                CRUD::set($updatedCountKey, CRUD::get($updatedCountKey) ?? 0);

                $entry = $uploader->processFileUpload($entry);

                CRUD::set($updatedCountKey, CRUD::get($updatedCountKey) + 1);

                return $entry;
            });
        }

        $model::retrieved(function ($entry) use ($uploader) {
            $entry = $uploader->retrieveUploadedFile($entry);
        });

        $model::deleting(function ($entry) use ($uploader) {
            $uploader->deleteUploadedFile($entry);
        });

        app('UploadersRepository')->markAsHandled($uploader->getIdentifier());
    }

    /**
     * Function responsible for managing the event registering process.
     *
     * @param array|null $subfield
     * @return void
     */
    public function registerEvents(array|null $subfield = [])
    {
        if (! empty($subfield)) {
            $this->registerSubfieldEvent($subfield);
            return;
        }

        $attributes = $this->crudObject->getAttributes();
        $model = $attributes['model'] ?? get_class($this->crudObject->crud()->getModel());
        $uploader = $this->getUploader($attributes, $this->uploaderConfiguration);

        $this->setupModelEvents($model, $uploader);
        $this->setupUploadConfigsInCrudObject($uploader);
    }

    /**
     * Register the events for subfields. This is a bit different than the main field because we need to
     * register the events for the base field, that may contain multiple subfields with uploads.
     *
     * @param array $subfield
     * @return void
     */
    public function registerSubfieldEvent(array $subfield)
    {
        $uploader = $this->getUploader($subfield, $this->uploaderConfiguration);
        $crudObject = $this->crudObject->getAttributes();
        $uploader = $uploader->repeats($crudObject['name']);

        // If this uploader is already registered bail out. We may endup here multiple times when doing modifications to the crud object.
        // Changing `subfields` properties will call the macros again. We prevent duplicate entries by checking
        // if the uploader is already registered.
        if (app('UploadersRepository')->isUploadRegistered($uploader->getRepeatableContainerName(), $uploader)) {
            return;
        }

        $model = $subfield['baseModel'] ?? $crudObject['model'] ?? get_class($this->crudObject->crud()->getModel());

        if (isset($crudObject['relation_type']) && $crudObject['entity'] !== false) {
            $uploader = $uploader->relationship(true);
        }

        // for subfields, we only register one event so that we have access to the repeatable container name.
        // all the uploaders for a given container are stored in the UploadersRepository.
        if (! app('UploadersRepository')->hasUploadersRegisteredFor($uploader->getRepeatableContainerName())) {
            $this->setupModelEvents($model, $uploader);
        }

        $subfields = collect($this->crudObject->getAttributes()['subfields']);
        $subfields = $subfields->map(function ($item) use ($subfield, $uploader) {
            if ($item['name'] === $subfield['name']) {
                $item['upload'] = true;
                $item['disk'] = $uploader->getDisk();
                $item['prefix'] = $uploader->getPath();
                if ($uploader->getTemporary()) {
                    $item['temporary'] = $uploader->getTemporary();
                    $item['expiration'] = $uploader->getExpiration();
                }
            }

            return $item;
        })->toArray();

        app('UploadersRepository')->registerUploader($uploader->getRepeatableContainerName(), $uploader);

        $this->crudObject->subfields($subfields);
    }

    /**
     * Return the uploader for the object beeing configured.
     * We will give priority to any uploader provided by `uploader => App\SomeUploaderClass` on upload definition.
     *
     * If none provided, we will use the Backpack defaults for the given object type.
     *
     * Throws an exception in case no uploader for the given object type is found.
     *
     * @param  array  $crudObject
     * @param  array  $uploaderConfiguration
     * @return UploaderInterface
     *
     * @throws Exception
     */
    private function getUploader(array $crudObject, array $uploaderConfiguration)
    {
        if (isset($uploaderConfiguration['uploaderType'])) {
            return $uploaderConfiguration['uploaderType']::for($crudObject, $uploaderConfiguration);
        }

        if (app('UploadersRepository')->hasUploadFor($crudObject['type'], $this->macro)) {
            return app('UploadersRepository')->getUploadFor($crudObject['type'], $this->macro)::for($crudObject, $uploaderConfiguration);
        }

        throw new Exception('Undefined upload type for '.$this->crudObjectType.' type: '.$crudObject['type']);
    }

    /**
     * Set up the upload attributes in the field/column.
     *
     * @param  UploaderInterface  $uploader
     * @return void
     */
    private function setupUploadConfigsInCrudObject(UploaderInterface $uploader)
    {
        $this->crudObject->upload(true)->disk($uploader->getDisk())->prefix($uploader->getPath());
    }
}
