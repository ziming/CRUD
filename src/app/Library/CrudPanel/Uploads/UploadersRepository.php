<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads;

final class UploadersRepository
{
    private array $uploaderClasses;

    private array $handledUploaders = [];

    private array $registeredUploaders = [];

    public function __construct()
    {
        $this->uploaderClasses = config('backpack.crud.uploaders');
    }

    public function markAsHandled(string $objectName)
    {
        if (! in_array($objectName, $this->handledUploaders)) {
            $this->handledUploaders[] = $objectName;
        }
    }

    public function isUploadHandled(string $objectName)
    {
        return in_array($objectName, $this->handledUploaders);
    }

    public function hasUploadFor(string $objectType, $group)
    {
        return array_key_exists($objectType, $this->uploaderClasses[$group]);
    }

    public function getUploadFor(string $objectType, $group)
    {
        return $this->uploaderClasses[$group][$objectType];
    }

    public function addUploaders(array $uploaders, $group)
    {
        $this->uploaderClasses[$group] = array_merge($this->getGroupUploaders($group), $uploaders);
    }

    private function getGroupUploaders($group)
    {
        return $this->uploaderClasses[$group] ?? [];
    }

    public function registerUploader(string $uploadName, $uploader)
    {
        if (! array_key_exists($uploadName, $this->registeredUploaders) || ! in_array($uploader, $this->registeredUploaders[$uploadName])) {
            $this->registeredUploaders[$uploadName][] = $uploader;
        }
    }

    public function hasUploadersRegisteredFor(string $uploadName)
    {
        return array_key_exists($uploadName, $this->registeredUploaders);
    }

    public function getRegisteredUploadersFor(string $uploadName)
    {
        return $this->registeredUploaders[$uploadName] ?? [];
    }

    public function isUploadRegistered($uploadName, $upload)
    {
        return $this->hasUploadersRegisteredFor($uploadName) && in_array($upload->getName(), $this->getRegisteredUploadNames($uploadName));
    }

    public function getRegisteredUploadNames(string $uploadName)
    {
        return array_map(function ($uploader) {
            return $uploader->getName();
        }, $this->getRegisteredUploadersFor($uploadName));
    }
}
