<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Support\Interfaces;

interface FileNameGeneratorInterface
{
    /**
     * Generate a unique file name.
     *
     * @param  string  $file
     * @return string
     */
    public function generate($file);
}
