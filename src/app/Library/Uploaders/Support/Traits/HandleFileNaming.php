<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support\Traits;

use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\FileNameGeneratorInterface;
use Illuminate\Http\UploadedFile;

trait HandleFileNaming
{
    public ?string $fileName = null;

    public FileNameGeneratorInterface $fileNameGenerator;

    public function getFileName(string|UploadedFile $file): string
    {
        if ($this->fileName) {
            return is_callable($this->fileName) ? ($this->fileName)($file, $this) : $this->fileName;
        }

        return $this->fileNameGenerator->getName($file);
    }

    private function getFileNameGeneratorInstance(?string $fileNameGenerator): FileNameGeneratorInterface
    {
        $fileGeneratorClass = $fileNameGenerator ?? config('backpack.crud.file_name_generator');

        if (! class_exists($fileGeneratorClass)) {
            throw new \Exception("The file name generator class [{$fileGeneratorClass}] does not exist.");
        }

        if (! in_array(FileNameGeneratorInterface::class, class_implements($fileGeneratorClass, false))) {
            throw new \Exception("The file name generator class [{$fileGeneratorClass}] must implement the [".FileNameGeneratorInterface::class.'] interface.');
        }

        return new $fileGeneratorClass();
    }
}
