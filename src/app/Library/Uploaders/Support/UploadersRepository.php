<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support;

use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;

final class UploadersRepository
{
    /**
     * The array of uploaders classes for field types.
     *
     * @var array
     */
    private array $uploaderClasses;

    /**
     * Uploaders registered in a repeatable group.
     *
     * @var array
     */
    private array $repeatableUploaders = [];

    /**
     * Uploaders that have already been handled (events registered) for each field/column instance.
     *
     * @var array
     */
    private array $handledUploaders = [];

    public function __construct()
    {
        $this->uploaderClasses = config('backpack.crud.uploaders');
    }

    /**
     * Mark the given uploader as handled.
     *
     * @param  string  $objectName  - the field/column name
     * @return void
     */
    public function markAsHandled(string $objectName)
    {
        if (! in_array($objectName, $this->handledUploaders)) {
            $this->handledUploaders[] = $objectName;
        }
    }

    /**
     * Check if the given uploader for field/column have been handled.
     *
     * @param  string  $objectName
     * @return bool
     */
    public function isUploadHandled(string $objectName)
    {
        return in_array($objectName, $this->handledUploaders);
    }

    /**
     * Check if there are uploads for the give object(field/column) type.
     *
     * @param  string  $objectType
     * @param  string  $group
     * @return bool
     */
    public function hasUploadFor(string $objectType, string $group)
    {
        return array_key_exists($objectType, $this->uploaderClasses[$group]);
    }

    /**
     * Return the uploader for the given object type.
     *
     * @param  string  $objectType
     * @param  string  $group
     * @return void
     */
    public function getUploadFor(string $objectType, string $group)
    {
        return $this->uploaderClasses[$group][$objectType];
    }

    /**
     * Register new uploaders or override existing ones.
     *
     * @param  array  $uploaders
     * @param  string  $group
     * @return void
     */
    public function addUploaderClasses(array $uploaders, string $group)
    {
        $this->uploaderClasses[$group] = array_merge($this->getGroupUploadersClasses($group), $uploaders);
    }

    /**
     * Return the uploaders classes for the given group.
     *
     * @param  string  $group
     * @return void
     */
    private function getGroupUploadersClasses(string $group)
    {
        return $this->uploaderClasses[$group] ?? [];
    }

    /**
     * Register the specified uploader for the given upload name.
     *
     * @param  string  $uploadName
     * @param  UploaderInterface  $uploader
     * @return void
     */
    public function registerRepeatableUploader(string $uploadName, UploaderInterface $uploader)
    {
        if (! array_key_exists($uploadName, $this->repeatableUploaders) || ! in_array($uploader, $this->repeatableUploaders[$uploadName])) {
            $this->repeatableUploaders[$uploadName][] = $uploader;
        }
    }

    /**
     * Check if there are uploaders registered for the given upload name.
     *
     * @param  string  $uploadName
     * @return bool
     */
    public function hasRepeatableUploadersFor(string $uploadName)
    {
        return array_key_exists($uploadName, $this->repeatableUploaders);
    }

    /**
     * Get the repeatable uploaders for the given upload name.
     *
     * @param  string  $uploadName
     * @return array
     */
    public function getRepeatableUploadersFor(string $uploadName)
    {
        return $this->repeatableUploaders[$uploadName] ?? [];
    }

    /**
     * Check if the specified upload is registered for the given repeatable uploads.
     *
     * @param  string  $uploadName
     * @param  UploaderInterface  $upload
     * @return bool
     */
    public function isUploadRegistered(string $uploadName, UploaderInterface $upload)
    {
        return $this->hasRepeatableUploadersFor($uploadName) && in_array($upload->getName(), $this->getRegisteredUploadNames($uploadName));
    }

    /**
     * Return the registered uploaders names for the given repeatable upload name.
     *
     * @param  string  $uploadName
     * @return array
     */
    public function getRegisteredUploadNames(string $uploadName)
    {
        return array_map(function ($uploader) {
            return $uploader->getName();
        }, $this->getRepeatableUploadersFor($uploadName));
    }
}
