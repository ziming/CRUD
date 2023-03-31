<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support\Interfaces;

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
