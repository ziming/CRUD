<?php

namespace Backpack\CRUD\app\Library\Uploaders\Validation;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Closure;

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

        // `upload_multiple` sends [[0 => null]] when user doesn't upload anything
        // assume that nothing changed on field so nothing is sent on the request.
        if (count($value) === 1 && empty($value[0])) {
            unset($this->data[$attribute]);
            $value = [];
        }
                   
        $previousValues = $this->entry?->{$attribute} ?? [];
        if (is_string($previousValues)) {
            $previousValues = json_decode($previousValues, true);
        }

        $value = array_merge($previousValues, $value);

        // if user uploaded something add it to the data beeing validated.
        if(!empty($value)) {
            $this->data[$attribute] = $value;
        }
       
        if ($this->entry) {
            $filesDeleted = CrudPanelFacade::getRequest()->input('clear_'.$attribute) ?? [];

            $data = $this->data;
            $data[$attribute] = array_diff($value, $filesDeleted);
            
            $this->validateArrayData($attribute, $fail, $data);

            $this->validateFiles($attribute, $value, $fail);

            return;
        }

        $this->validateArrayData($attribute, $fail);
       
        $this->validateFiles($attribute, $value, $fail);
    }
}
