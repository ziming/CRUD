<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Support;

use Backpack\CRUD\app\Library\CrudPanel\Uploads\Support\Interfaces\FileNameGeneratorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileNameGenerator implements FileNameGeneratorInterface
{
    /**
     * Generate a unique file name.
     *
     * @param  string|UploadedFile  $file
     * @return string
     */
    public function generate($file)
    {
        return $this->getFileName($file).'.'.$this->getExtensionFromFile($file);
    }

    /**
     * Return the file extension.
     *
     * @param  mixed  $file
     * @return string
     */
    private function getExtensionFromFile($file)
    {
        return is_a($file, UploadedFile::class, true) ? $file->extension() : Str::after(mime_content_type($file), '/');
    }

    /**
     * Return the file name.
     *
     * @param  mixed  $file
     * @return string
     */
    private function getFileName($file)
    {
        if (is_file($file)) {
            return Str::of($file->getClientOriginalName())->beforeLast('.')->slug()->append('-'.Str::random(4));
        }

        return Str::random(40);
    }
}
