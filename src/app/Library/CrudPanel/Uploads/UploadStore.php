<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads;

final class UploadStore
{
    private array $uploaders;

    private array $handledUploaders = [];

    public function __construct()
    {
        $this->uploaders = config('backpack.base.uploaders');
    }

    public function markAsHandled(string $objectName)
    {
        $this->handledUploaders[] = $objectName;
    }

    public function isUploadHandled(string $objectName)
    {
        return in_array($objectName, $this->handledUploaders);
    }

    public function hasUploadFor(string $objectType)
    {
        return array_key_exists($objectType, $this->uploaders);
    }

    public function getUploadFor(string $objectType)
    {
        return $this->uploaders[$objectType];
    }

    public function addUploaders(array $uploaders)
    {
        $this->uploaders = array_merge($this->uploaders, $uploaders);
    }
}
