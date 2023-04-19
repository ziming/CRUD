<?php

namespace Backpack\CRUD\app\Library\Uploaders\Validation;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Closure;
use Symfony\Component\HttpFoundation\File\File;

class ValidDropzone extends ValidBackpackUpload
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $temporaryFolder = config('backpack.base.temp_upload_folder_name') ?? 'backpack/temp/';
        $temporaryDisk = config('backpack.base.temp_disk_name') ?? 'public';
        $entry = CrudPanelFacade::getCurrentEntry();

        if(!is_array($value)) {
            try {
                $value = json_decode($value, true);
            }catch(\Exception $e) {
                $fail('Unable to determine the value type');
                return;
            }
        }

        if(!$entry) {
            $validator = Validator::make([$attribute => $value], [
                $attribute => $this->arrayRules,
            ], $this->validator->customMessages, $this->validator->customAttributes);

            if ($validator->fails()) {
                $fail($validator->errors()->first($attribute));
                return;
            }

            foreach($value as $file) {
                if (is_file($file)) {
                    $validator = Validator::make([$attribute => $file], [
                        $attribute => $this->fileRules,
                    ], $this->validator->customMessages, $this->validator->customAttributes);

                    if ($validator->fails()) {
                        $fail($validator->errors()->first($attribute));
                    }
                }   
            }
            return;
        }

        if (CrudPanelFacade::getRequest()->wantsJson()) {
            $this->validateFileUpload($attribute, $value, $fail);
            return;
        }

        $newFiles = array_filter($value, function($file) use ($temporaryDisk, $temporaryFolder) {
            //dump($file);
            return strpos($file, $temporaryFolder) !== false && Storage::disk($temporaryDisk)->exists($file);
        });
        
        dump($newFiles, $value);
    
        $previousFiles = CrudPanelFacade::getCurrentEntry()->{$attribute} ?? [];

        if(is_string($previousFiles) && !isset(CrudPanelFacade::getCurrentEntry()->getCasts()[$attribute])) {
            $previousFiles = json_decode($previousFiles, true);
        }

        $oldFiles = array_filter($value, function($file) use ($temporaryDisk, $temporaryFolder) {
            return strpos($file, $temporaryFolder) === false;
        });

        $filesToClear = array_diff($previousFiles, $oldFiles);

        $previousFilesWithoutCleared = array_diff($previousFiles, $filesToClear);
       
        // we are now going to check if the previous files - deleted files + new files still pass the validation 
        $previousFilesWithoutClearedPlusNewFiles[$attribute] = array_merge($previousFilesWithoutCleared, $newFiles);
        
        $validator = Validator::make($previousFilesWithoutClearedPlusNewFiles, [
                $attribute => $this->arrayRules,
            ], $this->validator->customMessages, $this->validator->customAttributes);

        if ($validator->fails()) {
            $fail($validator->errors()->first($attribute));
            return;
        }        
    }

    private function validateFileUpload(string $attribute, mixed $value, Closure $fail)
    {
        foreach($value as $file) {
            $validator = Validator::make([$attribute => $file], [
                $attribute => $this->fileRules,
            ], $this->validator->customMessages, $this->validator->customAttributes);

            if ($validator->fails()) {
                $fail($validator->errors()->first($attribute));
            }
        } 
    }
}
