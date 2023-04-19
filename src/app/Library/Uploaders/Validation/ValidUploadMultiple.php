<?php

namespace Backpack\CRUD\app\Library\Uploaders\Validation;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Closure;
use Illuminate\Support\Facades\Validator;

class ValidUploadMultiple extends ValidBackpackUpload
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
        if (! is_array($value)) {
            try {
                $value = json_decode($value, true);
            } catch(\Exception $e) {
                $fail('Unable to determine the value type');

                return;
            }
        }
        // `upload_multiple` sends [[0 => null]] (null after `ConvertEmptyStringsToNull`) when nothing changes on the field
        // for that reason we need to manually check what we are getting from the request
        if (CrudPanelFacade::getCurrentEntry() !== false) {
            $filesToClear = CrudPanelFacade::getRequest()->input('clear_'.$attribute) ?? [];
            $previousFiles = CrudPanelFacade::getCurrentEntry()->{$attribute} ?? [];

            if (is_string($previousFiles) && ! isset(CrudPanelFacade::getCurrentEntry()->getCasts()[$attribute])) {
                $previousFiles = json_decode($previousFiles, true);
            }

            $previousFilesWithoutCleared[$attribute] = array_diff($previousFiles, $filesToClear);

            // we are only going to check if the deleted files could break the validation rules
            if (count($value) === 1 && empty($value[0])) {
                $validator = Validator::make($previousFilesWithoutCleared, [
                    $attribute => $this->arrayRules,
                ], $this->validator->customMessages, $this->validator->customAttributes);

                if ($validator->fails()) {
                    $fail($validator->errors()->first($attribute));
                }

                return;
            }

            // we are now going to check if the previous files - deleted files + new files still pass the validation
            $previousFilesWithoutClearedPlusNewFiles[$attribute] = array_merge($previousFilesWithoutCleared[$attribute], $value);

            $validator = Validator::make($previousFilesWithoutClearedPlusNewFiles, [
                $attribute => $this->arrayRules,
            ], $this->validator->customMessages, $this->validator->customAttributes);

            if ($validator->fails()) {
                $fail($validator->errors()->first($attribute));

                return;
            }
        }

        // we are now going to perform the file validation on the actual files
        foreach ($value as $file) {
            $validator = Validator::make([$attribute => $file], [
                $attribute => $this->fileRules,
            ], $this->validator->customMessages, $this->validator->customAttributes);

            if ($validator->fails()) {
                $fail($validator->errors()->first($attribute));
            }
        }
    }
}
