<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidRepeatable extends ValidNestedArray
{
    public static function fieldRules(string|array|ValidationRule|Rule $rules): self
    {
        $instance = new static();
        $instance->arrayRules($rules);

        return $instance;
    }
}
