<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads;

final class UploadersRepository
{
    private array $uploaders;

    private array $handledUploaders = [];

    public function __construct()
    {
        $this->uploaders = config('backpack.crud.uploaders');
    }

    public function markAsHandled(string $objectName)
    {
        $this->handledUploaders[] = $objectName;
    }

    public function isUploadHandled(string $objectName)
    {
        return in_array($objectName, $this->handledUploaders);
    }

    public function hasUploadFor(string $objectType, $group)
    {
        return array_key_exists($objectType, $this->uploaders[$group]);
    }

    public function getUploadFor(string $objectType, $group)
    {
        return $this->uploaders[$group][$objectType];
    }

    public function addUploaders(array $uploaders, $group)
    {
        $this->uploaders[$group] = array_merge($this->getGroupUploaders($group), $uploaders);
    }

    private function getGroupUploaders($group)
    {
        return $this->uploaders[$group] ?? [];
    }
}
