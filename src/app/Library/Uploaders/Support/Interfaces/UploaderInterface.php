<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface UploaderInterface
{
    /**
     * Method called by `saving` event.
     *
     * @param Model $entry
     * @return void
     */
    public function processFileUpload(Model $entry);

    /**
     * Method called by `retrieved` event.
     *
     * @param Model $entry
     * @return void
     */
    public function retrieveUploadedFile(Model $entry);

    /**
     * Method called by `deleting` event.
     *
     * @param Model $entry
     * @return void
     */
    public function deleteUploadedFile(Model $entry);

    /**
     * Static constructor function
     *
     * @param array $field
     * @param array $configuration
     * @return void
     */
    public static function for(array $field, array $configuration);

    /**
     * Called to upload a single file
     *
     * @param Model $entry
     * @param mixed $values
     * @return void
     */
    public function uploadFile(Model $entry, $values = null);

    /**
     * Called to upload a group of repeatable files
     *
     * @param Model $entry
     * @param mixed $values
     * @return void
     */
    public function uploadRepeatableFile(Model $entry, $values = null);

    /**
     * Configures the repeatable settings on the uploader.
     *
     * @param string $repeatableContainerName
     * @return self
     */
    public function repeats(string $repeatableContainerName): self;

    /**
     * Configures the relationship settings on the uploader.
     *
     * @param bool $isRelation
     * @return self
     */
    public function relationship(bool $isRelation): self;

    public function getName();

    public function getDisk();

    public function getPath();

    public function getTemporary();

    public function getExpiration();

    public function getFileName($file);

    public function getRepeatableContainerName();

    public function getIdentifier();

    /**
     * Return the `deleteWhenEntryIsDeleted` property value
     *
     * @return bool
     */
    public function shouldDeleteFiles();
}
