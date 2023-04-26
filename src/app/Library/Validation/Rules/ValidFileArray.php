<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

abstract class ValidFileArray extends ValidArray
{
    public static function fileRules($rules): self
    {
        $instance = new static();
        $instance->itemRules($rules);

        return $instance;
    }
}
