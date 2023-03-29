<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

trait HandleRepeatableUploads
{
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
     * A function that uploaders can implement if the uploader is also supported in repeatable containers.
     *
     * @param  Model  $entry
     * @param  mixed  $values
     * @return mixed
     */
    public function uploadRepeatableFile(Model $entry, $values = null)
    {
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
     * Returns the repeatable container name.
     *
     * @return void
     */
    public function getRepeatableContainerName()
    {
        return $this->repeatableContainerName;
    }

    /**
     * Prepares the repeatable values from request and send them to the corresponding saving process.
     *
     * @param Model $entry
     * @return Model
     */
    private function handleRepeatableFiles(Model $entry)
    {
        $values = collect(CRUD::getRequest()->get($this->repeatableContainerName));
        $files = collect(CRUD::getRequest()->file($this->repeatableContainerName));
        $value = $this->mergeValuesRecursive($values, $files);

        if ($this->isRelationship) {
            return $this->uploadRelationshipFiles($entry, $value);
        } 

        $entry->{$this->repeatableContainerName} = json_encode($this->processRepeatableUploads($entry, $value));

        return $entry;
       
    }

    /**
     * Uploads the files for a relationship managed with repeatable interface.
     *
     * @param Model $entry
     * @param mixed $value
     * @return Model
     */
    private function uploadRelationshipFiles(Model $entry, mixed $value){
        $modelCount = CRUD::get('uploaded_'.$this->repeatableContainerName.'_count');
        $value = $value->slice($modelCount, 1)->toArray();

        foreach (app('UploadersRepository')->getRegisteredUploadersFor($this->repeatableContainerName) as $uploader) {
            if (array_key_exists($modelCount, $value) && isset($value[$modelCount][$uploader->getName()])) {
                $entry->{$uploader->getName()} = $uploader->uploadFile($entry, $value[$modelCount][$uploader->getName()]);
            }
        }

        return $entry;
    }

    /**
     * Handle the repeatable files uploads.
     *
     * @param Model $entry
     * @param mixed $value
     * @return mixed
     */
    private function processRepeatableUploads(Model $entry, $value)
    {
        foreach (app('UploadersRepository')->getRegisteredUploadersFor($this->repeatableContainerName) as $uploader) {
            $uploadedValues = $uploader->uploadRepeatableFile($entry, $value->pluck($uploader->getName())->toArray());

            $value = $value->map(function ($item, $key) use ($uploadedValues, $uploader) {
                $item[$uploader->getName()] = $uploadedValues[$key] ?? null;

                return $item;
            });
        }

        return $value;
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
            $previousValues = array_column($previousValues, $this->getName());
        }

        return $previousValues ?? [];
    }

    /**
     * Repeatable items send _order_ parameter in the request.
     * This olds the order of the items in the repeatable container.
     *
     * @return array
     */
    protected function getFileOrderFromRequest()
    {
        $items = CRUD::getRequest()->input('_order_'.$this->repeatableContainerName) ?? [];

        array_walk($items, function (&$key, $value) {
            $requestValue = $key[$this->getName()] ?? null;
            $key = $this->isMultiple ? (is_string($requestValue) ? explode(',', $requestValue) : $requestValue) : $requestValue;
        });

        return $items;
    }    

    /**
     * Retrieve the repeatable container files.
     *
     * @param Model $entry
     * @return void
     */
    private function retrieveRepeatableFiles(Model $entry)
    {
        if ($this->isRelationship) {
            return $this->retrieveFile($entry);
        }

        return $entry;
    }

    /**
     * Deletes the repeatable container files.
     *
     * @param Model $entry
     * @return void
     */
    private function deleteRepeatableFiles(Model $entry)
    {
        if ($this->isRelationship) {
            $this->deleteFiles($entry);
            return;
        }

        $repeatableValues = collect($entry->{$this->getName()});
        foreach (app('UploadersRepository')->getRegisteredUploadersFor($this->repeatableContainerName) as $upload) {
            if (! $upload->shouldDeleteFiles()) {
                continue;
            }
            $values = $repeatableValues->pluck($upload->getName())->toArray();
            foreach ($values as $value) {
                if (! $value) {
                    continue;
                }
                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        Storage::disk($upload->getDisk())->delete($upload->getPath().$subvalue);
                    }

                    continue;
                }
                Storage::disk($upload->getDisk())->delete($upload->getPath().$value);
            }
        }
    }

    /**
     * Given two multidimensional arrays, merge them recursively.
     *
     * @param array|Collection $array1
     * @param array|Collection $array2
     * @return array|Collection
     */
    private function mergeValuesRecursive($array1, $array2)
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
}