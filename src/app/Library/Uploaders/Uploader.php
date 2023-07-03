<?php

namespace Backpack\CRUD\app\Library\Uploaders;

use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Backpack\CRUD\app\Library\Uploaders\Support\Traits\HandleFileNaming;
use Backpack\CRUD\app\Library\Uploaders\Support\Traits\HandleRepeatableUploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class Uploader implements UploaderInterface
{
    use HandleFileNaming;
    use HandleRepeatableUploads;

    private string $name;

    private string $disk = 'public';

    private string $path = '';

    private bool $handleMultipleFiles = false;

    private bool $deleteWhenEntryIsDeleted = true;

    private bool|string $attachedToFakeField = false;

    /**
     * Cloud disks have the ability to generate temporary URLs to files, should we do it?
     */
    private bool $useTemporaryUrl = false;

    /**
     * When using temporary urls, define the time that the url will be valid.
     */
    private int $temporaryUrlExpirationTimeInMinutes = 1;

    /**
     * Indicates if the upload is relative to a relationship field/column.
     */
    private bool $isRelationship = false;

    public function __construct(array $crudObject, array $configuration)
    {
        $this->name = $crudObject['name'];
        $this->disk = $configuration['disk'] ?? $crudObject['disk'] ?? $this->disk;
        $this->path = $this->getPathFromConfiguration($crudObject, $configuration);
        $this->attachedToFakeField = $crudObject['fake'] ? ($crudObject['store_in'] ?? 'extras') : ($crudObject['store_in'] ?? false);
        $this->useTemporaryUrl = $configuration['temporaryUrl'] ?? $this->useTemporaryUrl;
        $this->temporaryUrlExpirationTimeInMinutes = $configuration['temporaryUrlExpirationTime'] ?? $this->temporaryUrlExpirationTimeInMinutes;
        $this->deleteWhenEntryIsDeleted = $configuration['deleteWhenEntryIsDeleted'] ?? $this->deleteWhenEntryIsDeleted;
        $this->fileNamer = is_callable($configuration['fileNamer'] ?? null) ? $configuration['fileNamer'] : $this->getFileNameGeneratorInstance($configuration['fileNamer'] ?? null);
    }

    /*******************************
     * Static methods
     *******************************/
    public static function for(array $crudObject, array $definition): UploaderInterface
    {
        return new static($crudObject, $definition);
    }

    /*******************************
     * public methods - event handler methods
     *******************************/
    public function storeUploadedFiles(Model $entry): Model
    {
        if ($this->handleRepeatableFiles) {
            return $this->handleRepeatableFiles($entry);
        }

        if($this->attachedToFakeField) {
            $fakeFieldValue = $entry->{$this->attachedToFakeField};
            $fakeFieldValue = is_string($fakeFieldValue) ? json_decode($fakeFieldValue, true) : (array)$fakeFieldValue;
            $fakeFieldValue[$this->getName()] = $this->uploadFiles($entry);
            $entry->{$this->attachedToFakeField} = json_encode($fakeFieldValue);
            return $entry;
        }
        
        $entry->{$this->getName()} = $this->uploadFiles($entry);

        return $entry;
    }

    public function retrieveUploadedFiles(Model $entry): Model
    {
        if ($this->handleRepeatableFiles) {
            return $this->retrieveRepeatableFiles($entry);
        }

        return $this->retrieveFiles($entry);
    }

    public function deleteUploadedFiles(Model $entry): void
    {
        if ($this->deleteWhenEntryIsDeleted) {
            if (! in_array(SoftDeletes::class, class_uses_recursive($entry), true)) {
                $this->performFileDeletion($entry);

                return;
            }

            if ($entry->isForceDeleting() === true) {
                $this->performFileDeletion($entry);
            }
        }
    }

    /*******************************
     * Getters
     *******************************/
    public function getName(): string
    {
        return $this->name;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function useTemporaryUrl(): bool
    {
        return $this->useTemporaryUrl;
    }

    public function getExpirationTimeInMinutes(): int
    {
        return $this->temporaryUrlExpirationTimeInMinutes;
    }

    public function shouldDeleteFiles(): bool
    {
        return $this->deleteWhenEntryIsDeleted;
    }

    public function getIdentifier(): string
    {
        if ($this->handleRepeatableFiles) {
            return $this->repeatableContainerName.'_'.$this->name;
        }

        return $this->name;
    }

    public function canHandleMultipleFiles(): bool
    {
        return $this->handleMultipleFiles;
    }

    /*******************************
     * Setters - fluently configure the uploader
     *******************************/
    public function multiple(): self
    {
        $this->handleMultipleFiles = true;

        return $this;
    }

    public function relationship(bool $isRelationship): self
    {
        $this->isRelationship = $isRelationship;

        return $this;
    }

    /*******************************
     * Default implementation functions
     *******************************/
    public function uploadFiles(Model $entry, $values = null)
    {
    }

    private function retrieveFiles(Model $entry): Model
    {
        $value = $entry->{$this->name};

        if ($this->handleMultipleFiles) {
            if (! isset($entry->getCasts()[$this->name]) && is_string($value)) {
                $entry->{$this->name} = json_decode($value, true);
            }

            return $entry;
        }

        $entry->{$this->name} = Str::after($value, $this->path);

        return $entry;
    }

    private function deleteFiles(Model $entry)
    {
        $values = $entry->{$this->name};

        if ($this->handleMultipleFiles) {
            // ensure we have an array of values when field is not casted in model.
            if (! isset($entry->getCasts()[$this->name]) && is_string($values)) {
                $values = json_decode($values, true);
            }
            foreach ($values as $value) {
                Storage::disk($this->disk)->delete($this->path.$value);
            }

            return;
        }

        $values = Str::after($values, $this->path);
        Storage::disk($this->disk)->delete($this->path.$values);
    }

    private function performFileDeletion(Model $entry)
    {
        if ($this->isRelationship || ! $this->handleRepeatableFiles) {
            $this->deleteFiles($entry);

            return;
        }

        $this->deleteRepeatableFiles($entry);
    }

    /*******************************
     * Private helper methods
     *******************************/
    private function getPathFromConfiguration(array $crudObject, array $configuration): string
    {
        $this->path = $configuration['path'] ?? $crudObject['prefix'] ?? $this->path;

        return empty($this->path) ? $this->path : Str::of($this->path)->finish('/')->value();
    }
}
