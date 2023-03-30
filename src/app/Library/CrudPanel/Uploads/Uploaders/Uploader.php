<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\Uploads\Interfaces\UploaderInterface;
use Backpack\CRUD\app\Library\CrudPanel\Uploads\Traits\HandleFileNaming;
use Backpack\CRUD\app\Library\CrudPanel\Uploads\Traits\HandleRepeatableUploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class Uploader implements UploaderInterface
{
    use HandleFileNaming;
    use HandleRepeatableUploads;

    /**
     * The name of the uploader AKA CrudField/Column name.
     *
     * @var string
     */
    private string $name;

    /**
     * Indicates the uploaded file should be deleted when entry is deleted.
     *
     * @var bool
     */
    private $deleteWhenEntryIsDeleted = true;

    /**
     * The disk where upload will be stored. By default `public`.
     *
     * @var string
     */
    private $disk = 'public';

    /**
     * Indicates if the upload handles multiple files.
     *
     * @var bool
     */
    private $isMultiple = false;

    /**
     * The path inside the disk to store the uploads.
     *
     * @var string
     */
    private $path = '';

    /**
     * Should the url to the object be a temporary one (eg: s3).
     *
     * @var bool
     */
    private $useTemporaryUrl = false;

    /**
     * When using temporary urls, defines the time that the url
     * should be available in minutes.
     *
     * By default 1 minute
     *
     * @var int
     */
    private $temporaryUrlExpirationTime = 1;

    /**
     * Indicates if the upload is relative to a relationship field/column.
     *
     * @var bool
     */
    private $isRelationship = false;

    final public function __construct(array $crudObject, array $configuration)
    {
        $this->name = $crudObject['name'];
        $this->disk = $configuration['disk'] ?? $crudObject['disk'] ?? $this->disk;
        $this->useTemporaryUrl = $configuration['temporary'] ?? $this->useTemporaryUrl;
        $this->temporaryUrlExpirationTime = $configuration['expiration'] ?? $this->temporaryUrlExpirationTime;
        $this->deleteWhenEntryIsDeleted = $configuration['whenDelete'] ?? $this->deleteWhenEntryIsDeleted;

        $this->path = $configuration['path'] ?? $crudObject['prefix'] ?? $this->path;
        $this->path = empty($this->path) ? $this->path : Str::of($this->path)->finish('/')->value();

        $this->setFileNameGenerator($configuration['fileNameGenerator'] ?? null);
        $this->fileName = $configuration['fileName'] ?? $this->fileName;
    }

    /**
     * An abstract function that all uploaders must implement for a single file save process.
     *
     * @param  Model  $entry
     * @param  mixed  $values
     * @return mixed
     */
    abstract public function uploadFile(Model $entry, $values = null);

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

    /**
     * Retrive the regular entry files.
     *
     * @param Model $entry
     * @return Model
     */
    protected function retrieveFile(Model $entry)
    {
        $value = $entry->{$this->name};

        if ($this->isMultiple && ! isset($entry->getCasts()[$this->name]) && is_string($value)) {
            $entry->{$this->name} = json_decode($value, true);

            return $entry;
        }

        $entry->{$this->name} = Str::after($value, $this->path);

        return $entry;
    }

    /**
     * The function called in the deleting event. It checks if the uploaded file should be deleted.
     *
     * @param  Model  $entry
     * @return void
     */
    public function deleteUploadedFile(Model $entry)
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

    /**
     * The function called in the retrieved event that handles the display of uploaded values.
     *
     * @param  Model  $entry
     * @return void
     */
    private function deleteFiles($entry)
    {
        $values = $entry->{$this->name};

        if ($this->isMultiple) {
            // ensure we have an array of values when field is not casted in model.
            if (! isset($entry->getCasts()[$this->name]) && is_string($values)) {
                $values = json_decode($values, true);
            }
            foreach ($values as $value) {
                Storage::disk($this->disk)->delete($this->path.$value);
            }

            return;
        }

        $values = (array) Str::after($values, $this->path);
        Storage::disk($this->disk)->delete($this->path.$values);
    }

    /**
     * When the file should be deleted, this function is called to delete the file using the
     * appropriate delete method depending on some uploader properties.
     *
     * @param  Model  $entry
     * @return void
     */
    private function performFileDeletion($entry)
    {
        if ($this->isRelationship || ! $this->isRepeatable) {
            $this->deleteFiles($entry);

            return;
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
     * Should the files be deleted when the entry is deleted.
     *
     * @return bool
     */
    public function shouldDeleteFiles()
    {
        return $this->deleteWhenEntryIsDeleted;
    }

    /**
     * Return the uploader identifier. Either the name or the combination of the repeatable container name and the name.
     *
     * @return void
     */
    public function getIdentifier()
    {
        if ($this->isRepeatable) {
            return $this->repeatableContainerName.'_'.$this->name;
        }

        return $this->name;
    }
}
