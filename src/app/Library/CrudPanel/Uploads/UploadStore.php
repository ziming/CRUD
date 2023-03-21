<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads;

final class UploadStore
{
    private array $uploaders;

    private array $handledUploaders = [];

    private const DEFAULT_GROUP = 'backpack';

    public function __construct()
    {
        $this->uploaders[self::DEFAULT_GROUP] = [
            'image'           => \Backpack\CRUD\app\Library\CrudPanel\Uploads\Uploaders\SingleBase64Image::class,
            'upload'          => \Backpack\CRUD\app\Library\CrudPanel\Uploads\Uploaders\SingleFile::class,
            'upload_multiple' => \Backpack\CRUD\app\Library\CrudPanel\Uploads\Uploaders\MultipleFiles::class,
            'repeatable'      => \Backpack\CRUD\app\Library\CrudPanel\Uploads\Uploaders\RepeatableUploader::class,
        ];
    }

    public function markAsHandled(string $objectName)
    {
        $this->handledUploaders[] = $objectName;
    }

    public function isUploadHandled(string $objectName)
    {
        return in_array($objectName, $this->handledUploaders);
    }

    public function hasUploadFor(string $objectType, $group = null)
    {
        $group = $group ?? self::DEFAULT_GROUP;

        return array_key_exists($objectType, $this->uploaders[$group]);
    }

    public function getUploadFor(string $objectType, $group = null)
    {
        $group = $group ?? self::DEFAULT_GROUP;

        return $this->uploaders[$group][$objectType];
    }

    public function addUploaders(array $uploaders, $group = null)
    {
        $group = $group ?? self::DEFAULT_GROUP;

        $this->uploaders[$group] = array_merge($this->getGroupUploaders($group), $uploaders);
    }

    private function getGroupUploaders($group = null)
    {
        $group = $group ?? self::DEFAULT_GROUP;

        return $this->uploaders[$group] ?? [];
    }
}
