<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Closure;
use Illuminate\Support\Facades\Validator;

class ValidUpload extends BackpackCustomRule
{
    public array $fileRules = [];
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!array_key_exists($attribute, $this->data) && $this->entry) {
            return;
        }

        $this->validateAttributeRules($attribute, $value, $fail);
        
        if(!empty($value) && !empty($this->fileRules)) {
            $validator = Validator::make([$attribute => $value], [
                $attribute => $this->fileRules,
            ], $this->validator->customMessages, $this->validator->customAttributes);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages()[$attribute] as $message) {
                    $fail($message)->translate();
                }
            }
        }
    }

    public function fileRules($rules): self
    {
        $this->fileRules = self::prepareRules($rules);

        return $this;
    }
}
