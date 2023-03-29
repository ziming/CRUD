<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Traits;

use Backpack\CRUD\app\Library\CrudPanel\Uploads\Support\Interfaces\FileNameGeneratorInterface;
use Illuminate\Http\UploadedFile;

trait HandleFileNaming
{
    /**
     * Developer provided filename.
     *
     * @var null|string|\Closure
     */
    public $fileName = null;

    /**
     * The file name generator.
     *
     * @var FileNameGeneratorInterface
     */
    public $fileNameGenerator;

    /**
     * Returns the file generator class.
     *
     * @param  null|string  $fileNameGenerator
     * @return void
     */
    private function setFileNameGenerator($fileNameGenerator)
    {
        $fileGeneratorClass = $fileNameGenerator ?? config('backpack.crud.file_name_generator');

        if (! class_exists($fileGeneratorClass)) {
            throw new \Exception("The file name generator class [{$fileGeneratorClass}] does not exist.");
        }

        if (! class_implements($fileGeneratorClass, FileNameGeneratorInterface::class)) {
            throw new \Exception("The file name generator class [{$fileGeneratorClass}] must implement the [".FileNameGeneratorInterface::class.'] interface.');
        }

        $this->fileNameGenerator = new $fileGeneratorClass();
    }

    /**
     * Return the file name.
     *
     * @param  string|UploadedFile  $file
     * @return string
     */
    public function getFileName($file)
    {
        if ($this->fileName) {
            return is_callable($this->fileName) ? ($this->fileName)($file, $this) : $this->fileName;
        }

        return $this->fileNameGenerator->generate($file);
    }
}
