<?php

namespace Backpack\CRUD\app\Library\Uploaders\Validation;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Rules\File;

class ValidBackpackUpload implements ValidationRule, DataAwareRule, ValidatorAwareRule
{
    /**
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    protected array $data;

    public $arrayRules = [];

    public $fileRules = [];

    public static function make(): self
    {
        return new static();
    }

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
    }

    public function arrayRules(string|array|File $rules): self
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        if (! in_array('array', $rules)) {
            $rules[] = 'array';
        }

        $this->arrayRules = $rules;

        return $this;
    }

    public function fileRules(string|array|File $rules): self
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        if (! is_array($rules)) {
            $rules = [$rules];
        }
        $this->fileRules = $rules;

        return $this;
    }

    /**
     * Set the performing validator.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
