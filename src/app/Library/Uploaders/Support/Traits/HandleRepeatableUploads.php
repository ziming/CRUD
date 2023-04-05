<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

trait HandleRepeatableUploads
{
    public bool $handleRepeatableFiles = false;

    public ?string $repeatableContainerName = null;

    /*******************************
     * Setters - fluently configure the uploader
     *******************************/
    public function repeats(string $repeatableContainerName): self
    {
        $this->handleRepeatableFiles = true;

        $this->repeatableContainerName = $repeatableContainerName;

        return $this;
    }

    /*******************************
     * Getters
     *******************************/
    public function getRepeatableContainerName(): ?string
    {
        return $this->repeatableContainerName;
    }

    /*******************************
     * Default implementation methods
     *******************************/
    protected function uploadRepeatableFiles(Model $entry, $values, $previousValues)
    {

    }

    private function handleRepeatableFiles(Model $entry): Model
    {
        $values = collect(CRUD::getRequest()->get($this->getRepeatableContainerName()));
        $files = collect(CRUD::getRequest()->file($this->getRepeatableContainerName()));
        $value = $this->mergeValuesRecursive($values, $files);

        if ($this->isRelationship) {
            return $this->uploadRelationshipFiles($entry, $value);
        }

        $entry->{$this->getRepeatableContainerName()} = json_encode($this->processRepeatableUploads($entry, $value));

        return $entry;
    }

    private function uploadRelationshipFiles(Model $entry, mixed $value): Model
    {
        $modelCount = CRUD::get('uploaded_'.$this->getRepeatableContainerName().'_count');
        $value = $value->slice($modelCount, 1)->toArray();

        foreach (app('UploadersRepository')->getRepeatableUploadersFor($this->getRepeatableContainerName()) as $uploader) {
            if (array_key_exists($modelCount, $value) && isset($value[$modelCount][$uploader->getName()])) {
                $entry->{$uploader->getName()} = $uploader->uploadFiles($entry, $value[$modelCount][$uploader->getName()]);
            }
        }

        return $entry;
    }

    private function processRepeatableUploads(Model $entry, Collection $value): Collection
    {
        foreach (app('UploadersRepository')->getRepeatableUploadersFor($this->getRepeatableContainerName()) as $uploader) {
            $uploadedValues = $uploader->uploadRepeatableFiles($entry, $value->pluck($uploader->getName())->toArray(), $this->getPreviousRepeatableValues($entry, $uploader));

            $value = $value->map(function ($item, $key) use ($uploadedValues, $uploader) {
                $item[$uploader->getName()] = $uploadedValues[$key] ?? null;

                return $item;
            });
        }

        return $value;
    }

    private function retrieveRepeatableFiles(Model $entry): Model
    {
        if ($this->isRelationship) {
            return $this->retrieveFiles($entry);
        }

        return $entry;
    }

    private function deleteRepeatableFiles(Model $entry): void
    {
        if ($this->isRelationship) {
            $this->deleteFiles($entry);

            return;
        }

        $repeatableValues = collect($entry->{$this->getName()});
        foreach (app('UploadersRepository')->getRepeatableUploadersFor($this->getRepeatableContainerName()) as $upload) {
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
    /*******************************
     * Helper methods
     *******************************/

    /**
     * Given two multidimensional arrays/collections, merge them recursively.
     */
    private function mergeValuesRecursive(array|Collection $array1, array|Collection $array2): array|Collection
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

    /**
     * Repeatable items send `_order_` parameter in the request.
     * This holds the order of the items in the repeatable container.
     */
    protected function getFileOrderFromRequest(): array
    {
        $items = CRUD::getRequest()->input('_order_'.$this->getRepeatableContainerName()) ?? [];

        array_walk($items, function (&$key, $value) {
            $requestValue = $key[$this->getName()] ?? null;
            $key = $this->handleMultipleFiles ? (is_string($requestValue) ? explode(',', $requestValue) : $requestValue) : $requestValue;
        });

        return $items;
    }

    private function getPreviousRepeatableValues(Model $entry, UploaderInterface $uploader): array
    {
        $previousValues = json_decode($entry->getOriginal($uploader->getRepeatableContainerName()), true);

        if (! empty($previousValues)) {
            $previousValues = array_column($previousValues, $uploader->getName());
        }

        return $previousValues ?? [];
    }
}
