<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface UploaderInterface
{
    public function processFileUpload(Model $entry);

    public function retrieveUploadedFile(Model $entry);

    public function deleteUploadedFile(Model $entry);

    public static function for(array $field, array $configuration);

    public function __construct(array $crudObject, array $configuration);

    public function uploadFile(Model $entry, $values = null);

    public function uploadRepeatableFile(Model $entry, $values = null);

    public function getRepeatableContainerName();

    public function getIdentifier();

    public function repeats(string $repeatableContainerName): self;

    public function relationship(bool $isRelation): self;

    public function getName();

    public function getDisk();

    public function getPath();

    public function getTemporary();

    public function getExpiration();
    
    public function getFileName($file);

    public function shouldDeleteFiles();
}
