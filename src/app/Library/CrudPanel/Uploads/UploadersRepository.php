<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads;

use Backpack\CRUD\app\Library\CrudPanel\Uploads\Interfaces\UploaderInterface;

final class UploadersRepository
{
    /**
     * The array of uploaders classes for field types.
     *
     * @var array
     */
    private array $uploaderClasses;

    /**
     * The array of uploaders that have been handled, aka events registered.
     *
     * @var array
     */
    private array $handledUploaders = [];

    /**
     * The array of uploaders that have been registered, aka would be handled if needed by the event register.
     *
     * @var array
     */
    private array $registeredUploaders = [];

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
    public function registerUploader(string $uploadName, UploaderInterface $uploader)
    {
        if (! array_key_exists($uploadName, $this->registeredUploaders) || ! in_array($uploader, $this->registeredUploaders[$uploadName])) {
            $this->registeredUploaders[$uploadName][] = $uploader;
        }
    }

    /**
     * Check if there are uploaders registered for the given upload name.
     *
     * @param  string  $uploadName
     * @return bool
     */
    public function hasUploadersRegisteredFor(string $uploadName)
    {
        return array_key_exists($uploadName, $this->registeredUploaders);
    }

    /**
     * Get the registered uploaders for the given upload name.
     *
     * @param  string  $uploadName
     * @return array
     */
    public function getRegisteredUploadersFor(string $uploadName)
    {
        return $this->registeredUploaders[$uploadName] ?? [];
    }

    /**
     * Check if the specified upload is registered for the given upload name.
     *
     * @param  string  $uploadName
     * @param  UploaderInterface  $upload
     * @return bool
     */
    public function isUploadRegistered(string $uploadName, UploaderInterface $upload)
    {
        return $this->hasUploadersRegisteredFor($uploadName) && in_array($upload->getName(), $this->getRegisteredUploadNames($uploadName));
    }

    /**
     * Return the registered uploaders names for the given upload name.
     *
     * @param  string  $uploadName
     * @return array
     */
    public function getRegisteredUploadNames(string $uploadName)
    {
        return array_map(function ($uploader) {
            return $uploader->getName();
        }, $this->getRegisteredUploadersFor($uploadName));
    }
}
