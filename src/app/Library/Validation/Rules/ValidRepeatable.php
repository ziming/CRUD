<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Backpack\CRUD\app\Library\Validation\Rules\ValidNestedArray;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Rule;

class ValidRepeatable extends ValidNestedArray
{
    public static function fieldRules(string|array|ValidationRule|Rule $rules): self
    {
        $instance = new static();
        $instance->arrayRules($rules);

        return $instance;
    }
}
