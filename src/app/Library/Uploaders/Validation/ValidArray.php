<?php

namespace Backpack\CRUD\app\Library\Uploaders\Validation;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;

class ValidArray implements ValidationRule, DataAwareRule, ValidatorAwareRule
{
    /**
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    protected array $data;

    public array $arrayRules = [];

    public array $itemRules = [];

    public ?Model $entry;

    public static function make(): self
    {
        $instance = new static();
        $entry = CrudPanelFacade::getCurrentEntry();
        $instance->entry = $entry !== false ? $entry : null;

        return $instance;
    }

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
        $this->validateArrayData($attribute, $value, $fail);
        $this->validateItemsAsArray($attribute, $value, $fail);
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

    /**
     * Set the rules that apply to the "array" aka the field, if it's required, min, max etc.
     */
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

    /**
     * Set the rules that apply to the items beeing sent in array.
     */
    public function itemRules(string|array|File $rules): self
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        if (! is_array($rules)) {
            $rules = [$rules];
        }
        $this->itemRules = $rules;

        return $this;
    }

    /**
     * Performs the validation on the array of items, item by item, using the item validation array.
     *
     * @param  string  $attribute
     * @param  array  $files
     * @param  Closure  $fail
     * @return void
     */
    protected function validateItems($attribute, $items, $fail)
    {
        foreach ($items as $item) {
            $validator = Validator::make([$attribute => $item], [
                $attribute => $this->itemRules,
            ], $this->validator->customMessages, $this->validator->customAttributes);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages()[$attribute] as $message) {
                    $fail($message)->translate();
                }
            }
        }
    }

    /**
     * Performs the validation on the array of items, using the item validation array.
     *
     * @param  string  $attribute
     * @param  array  $files
     * @param  Closure  $fail
     * @return void
     */
    protected function validateItemsAsArray($attribute, $items, $fail)
    {
        $validator = Validator::make([$attribute => $items], [
            $attribute.'.*' => $this->itemRules,
        ], $this->validator->customMessages, $this->validator->customAttributes);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages()[$attribute] as $message) {
                $fail($message)->translate();
            }
        }
    }

    /**
     * Validate the given data or the array of data from the validator againts the array rules.
     *
     * @param  string  $attribute
     * @param  Closure  $fail
     * @param  null|array  $data
     * @param  null|array  $rules
     * @return void
     */
    protected function validateArrayData($attribute, $fail, $data = null, $rules = null)
    {
        $data = $data ?? $this->data;
        $rules = $rules ?? $this->arrayRules;

        $validator = Validator::make($data, [
            $attribute => $rules,
        ], $this->validator->customMessages, $this->validator->customAttributes);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages()[$attribute] as $message) {
                $fail($message)->translate();
            }
        }
    }
}
