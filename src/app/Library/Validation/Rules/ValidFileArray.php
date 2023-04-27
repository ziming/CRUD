<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Rule;

abstract class ValidFileArray extends ValidArray
{   
    public function fileRules(string|array|ValidationRule|Rule $rules = []): self
    {   
        $this->itemRules($rules);
        return $this;
    }
}
