<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\CrudPanel\Uploads\Interfaces\UploaderInterface;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class Uploader implements UploaderInterface
{
    /**
     * The name of the uploader AKA CrudField/Column name.
     *
     * @var string
     */
    public string $name;

    /**
     * Indicates the uploaded file should be deleted when entry is deleted.
     *
     * @var bool
     */
    public $deleteWhenEntryIsDeleted = true;

    /**
     * Indicates if this uploader instance is inside a repeatable container.
     *
     * @var bool
     */
    public $isRepeatable = false;

    /**
     * When inside a repeatable container, indicates the container name.
     *
     * @var string|null
     */
    public $repeatableContainerName = null;

    /**
     * Developer provided filename.
     *
     * @var null|string|Closure
     */
    public $fileName = null;

    /**
     * The disk where upload will be stored. By default `public`.
     *
     * @var string
     */
    public $disk = 'public';

    /**
     * Indicates if the upload handles multiple files.
     *
     * @var bool
     */
    public $isMultiple = false;

    /**
     * The path inside the disk to store the uploads.
     *
     * @var string
     */
    public $path = '';

    /**
     * Should the url to the object be a temporary one (eg: s3).
     *
     * @var bool
     */
    public $useTemporaryUrl = false;

    /**
     * When using temporary urls, defines the time that the url
     * should be available in minutes.
     *
     * By default 1 minute
     *
     * @var int
     */
    public $temporaryUrlExpirationTime = 1;

    /**
     * Indicates if the upload is relative to a relationship field/column.
     *
     * @var bool
     */
    public $isRelationship = false;

    public function __construct(array $crudObject, array $configuration)
    {
        $this->name = $crudObject['name'];
        $this->disk = $configuration['disk'] ?? $crudObject['disk'] ?? $this->disk;
        $this->useTemporaryUrl = $configuration['temporary'] ?? $this->useTemporaryUrl;
        $this->temporaryUrlExpirationTime = $configuration['expiration'] ?? $this->temporaryUrlExpirationTime;
        $this->path = $configuration['path'] ?? $crudObject['prefix'] ?? $this->path;
        $this->path = empty($this->path) ? $this->path : Str::of($this->path)->finish('/')->value;
        $this->fileName = $configuration['fileName'] ?? $this->fileName;
        $this->deleteWhenEntryIsDeleted = $configuration['whenDelete'] ?? $this->deleteWhenEntryIsDeleted;
    }

    /**
     * An abstract function that all uploaders must implement with a single file save process.
     *
     * @param  Model  $entry
     * @param  mixed  $values
     * @return mixed
     */
    abstract public function uploadFile(Model $entry, $values = null);

    /**
     * An function that all uploaders must implement if it also supports repeatable files.
     *
     * @param  Model  $entry
     * @param  mixed  $values
     * @return mixed
     */
    public function uploadRepeatableFile(Model $entry, $values = null)
    {
    }

    public function getRepeatableContainerName()
    {
        return $this->repeatableContainerName;
    }

    /**
     * The function called in the saving event that starts the upload process.
     *
     * @param  Model  $entry
     * @return Model
     */
    public function processFileUpload(Model $entry)
    {
        if ($this->isRepeatable) {
            return $this->handleRepeatableFiles($entry);
        }

        $entry->{$this->name} = $this->uploadFile($entry);

        return $entry;
    }

    private function handleRepeatableFiles(Model $entry)
    {
        $values = collect(CRUD::getRequest()->get($this->repeatableContainerName));
        $files = collect(CRUD::getRequest()->file($this->repeatableContainerName));

        $value = $this->mergeValuesRecursive($values, $files);

        if (! $this->isRelationship) {
            $entry->{$this->repeatableContainerName} = json_encode($this->processRepeatableUploads($entry, $value));
        } else {
            $modelCount = CRUD::get('uploaded_'.$this->repeatableContainerName.'_count');

            $value = $value->slice($modelCount, 1)->toArray();

            foreach (app('UploadersRepository')->getRegisteredUploadersFor($this->repeatableContainerName) as $uploader) {
                if (array_key_exists($modelCount, $value) && isset($value[$modelCount][$uploader->getName()])) {
                    $entry->{$uploader->getName()} = $uploader->uploadFile($entry, $value[$modelCount][$uploader->getName()]);
                }
            }
        }

        return $entry;
    }

    private function processRepeatableUploads(Model $entry, $value)
    {
        foreach (app('UploadersRepository')->getRegisteredUploadersFor($this->repeatableContainerName) as $uploader) {
            $uploadedValues = $uploader->uploadRepeatableFile($entry, $value->pluck($uploader->name)->toArray());

            $value = $value->map(function ($item, $key) use ($uploadedValues, $uploader) {
                $item[$uploader->getName()] = $uploadedValues[$key] ?? null;

                return $item;
            });
        }

        return $value;
    }

    /**
     * Return the uploader name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the uploader disk.
     *
     * @return string
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * Return the uploader path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return the uploader temporary option.
     *
     * @return bool
     */
    public function getTemporary()
    {
        return $this->useTemporaryUrl;
    }

    /**
     * Return the uploader expiration time in minutes.
     *
     * @return int
     */
    public function getExpiration()
    {
        return $this->temporaryUrlExpirationTime;
    }

    /**
     * The function called in the retrieved event that handles the display of uploaded values.
     *
     * @param  Model  $entry
     * @return Model
     */
    public function retrieveUploadedFile(Model $entry)
    {
        if ($this->isRepeatable) {
            return $this->retrieveRepeatableFiles($entry);
        }

        return $this->retrieveFile($entry);
    }

    protected function retrieveFile($entry)
    {
        $value = $entry->{$this->name};

        if ($this->isMultiple && ! isset($entry->getCasts()[$this->name]) && is_string($value)) {
            $entry->{$this->name} = json_decode($value, true);
        } else {
            $entry->{$this->name} = Str::after($value, $this->path);
        }

        return $entry;
    }

    protected function retrieveRepeatableFiles($entry)
    {
        if ($this->isRelationship) {
            return $this->retrieveFile($entry);
        }

        return $entry;
    }

    /**
     * The function called in the deleting event to delete the uploaded files upon entry deletion.
     *
     * @param  Model  $entry
     * @return void
     */
    public function deleteUploadedFile(Model $entry)
    {
        if ($this->deleteWhenEntryIsDeleted) {
            if (! in_array(SoftDeletes::class, class_uses_recursive($entry), true)) {
                $this->performFileDeletion($entry);
            } else {
                if ($entry->forceDeleting === true) {
                    $this->performFileDeletion($entry);
                }
            }
        }
    }

    private function deleteRepeatableFiles($entry)
    {
        if ($this->isRelationship) {
            return $this->deleteFiles($entry);
        }

        $repeatableValues = collect($entry->{$this->getName()});
        foreach (app('UploadersRepository')->getRegisteredUploadersFor($this->repeatableContainerName) as $upload) {
            if (! $upload->deleteWhenEntryIsDeleted) {
                continue;
            }
            $values = $repeatableValues->pluck($upload->getName())->toArray();
            foreach ($values as $value) {
                if (! $value) {
                    continue;
                }
                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        Storage::disk($upload->disk)->delete($upload->path.$subvalue);
                    }

                    continue;
                }
                Storage::disk($upload->disk)->delete($upload->path.$value);
            }
        }
    }

    private function deleteFiles($entry)
    {
        $values = $entry->{$this->name};

        if ($this->isMultiple) {
            if (! isset($entry->getCasts()[$this->name]) && is_string($values)) {
                $values = json_decode($values, true);
            }
        } else {
            $values = (array) Str::after($values, $this->path);
        }

        foreach ($values as $value) {
            Storage::disk($this->disk)->delete($this->path.$value);
        }
    }

    private function performFileDeletion($entry)
    {
        if ($this->isRelationship) {
            return $this->deleteFiles($entry);
        }

        $this->deleteRepeatableFiles($entry);
    }

    /**
     * Build an uploader instance.
     *
     * @param  array  $crudObject
     * @param  array  $definition
     * @return self
     */
    public static function for(array $crudObject, array $definition)
    {
        return new static($crudObject, $definition);
    }

    /**
     * Set multiple attribute to true in the uploader.
     *
     * @return self
     */
    protected function multiple()
    {
        $this->isMultiple = true;

        return $this;
    }

    /**
     * Set relationship attribute in uploader.
     * When true, it also removes the repeatable in case the relationship is handled.
     *
     * @param  bool  $isRelationship
     * @return self
     */
    public function relationship(bool $isRelationship): self
    {
        $this->isRelationship = $isRelationship;

        return $this;
    }

    /**
     * Set the repeatable attribute to true in the uploader and the
     * corresponding container name.
     *
     * @param  string  $repeatableContainerName
     * @return self
     */
    public function repeats(string $repeatableContainerName): self
    {
        $this->isRepeatable = true;

        $this->repeatableContainerName = $repeatableContainerName;

        return $this;
    }

    /**
     * Repeatable items send _order_ parameter in the request.
     * This olds the information for uploads inside repeatable containers.
     *
     * @return array
     */
    protected function getFileOrderFromRequest()
    {
        $items = CRUD::getRequest()->input('_order_'.$this->repeatableContainerName) ?? [];

        array_walk($items, function (&$key, $value) {
            $requestValue = $key[$this->name] ?? null;
            $key = $this->isMultiple ? (is_string($requestValue) ? explode(',', $requestValue) : $requestValue) : $requestValue;
        });

        return $items;
    }

    /**
     * Return a new instance of the entry class for the uploader.
     *
     * @return Model
     */
    protected function modelInstance()
    {
        //return new $this->entryClass;
    }

    /**
     * Return the uploader stored values when in a repeatable container.
     *
     * @param  Model  $entry
     * @return array
     */
    protected function getPreviousRepeatableValues(Model $entry)
    {
        $previousValues = json_decode($entry->getOriginal($this->repeatableContainerName), true);
        if (! empty($previousValues)) {
            $previousValues = array_column($previousValues, $this->name);
        }

        return $previousValues ?? [];
    }

    /**
     * Return the file extension.
     *
     * @param  mixed  $file
     * @return string
     */
    protected function getExtensionFromFile($file)
    {
        return is_a($file, UploadedFile::class, true) ? $file->extension() : Str::after(mime_content_type($file), '/');
    }

    /**
     * Return the file name built by Backpack or by the developer in `fileName` configuration.
     *
     * @param  mixed  $file
     * @return string
     */
    protected function getFileName($file)
    {
        if (is_file($file)) {
            return Str::of($this->fileNameFrom($file) ?? Str::of($file->getClientOriginalName())->beforeLast('.')->slug()->append('-'.Str::random(4)));
        }

        return Str::of($this->fileNameFrom($file) ?? Str::random(40));
    }

    /**
     * Return the complete filename and extension.
     *
     * @param  mixed  $file
     * @return string
     */
    protected function getFileNameWithExtension($file)
    {
        if (is_file($file)) {
            return $this->getFileName($file).'.'.$this->getExtensionFromFile($file);
        }

        return Str::of($this->fileNameFrom($file) ?? Str::random(40)).'.'.$this->getExtensionFromFile($file);
    }

    /**
     * Allow developer to override the default Backpack fileName.
     *
     * @param  mixed  $file
     * @return string|null
     */
    private function fileNameFrom($file)
    {
        if (is_callable($this->fileName)) {
            return ($this->fileName)($file, $this);
        }

        return $this->fileName;
    }

    protected function mergeValuesRecursive($array1, $array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergeValuesRecursive($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public function getIdentifier()
    {
        if ($this->isRepeatable) {
            return $this->repeatableContainerName.'_'.$this->name;
        }

        return $this->name;
    }
}
