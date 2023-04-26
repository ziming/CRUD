<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Closure;
use Illuminate\Support\Facades\Validator;

class ValidUpload extends BackpackCustomRule
{
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
        if (! is_array($value)) {
            try {
                $value = json_decode($value, true);
            } catch (\Exception $e) {
                $fail('Unable to determine the value type.');

                return;
            }
        }

        $validator = Validator::make([$attribute => $value], [
            $attribute => $this->itemRules,
        ], $this->validator->customMessages, $this->validator->customAttributes);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages()[$attribute] as $message) {
                $fail($message)->translate();
            }
        }
    }
}
