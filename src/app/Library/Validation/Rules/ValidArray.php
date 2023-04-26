<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

abstract class ValidArray extends BackpackCustomRule
{
    public array $itemRules = [];

    public array $namedItemRules = [];

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

        $this->validateArrayData($attribute, $fail, $value);
        $this->validateItems($attribute, $value, $fail);
    }

    /**
     * Set the rules that apply to the "array" aka the field, if it's required, min, max etc.
     */
    public function arrayRules(string|array|ValidationRule|Rule $rules): self
    {
        $this->attributeRules = self::prepareRules($rules);

        if(!in_array('array', $this->attributeRules)) {
            $this->attributeRules[] = 'array';
        }

        return $this;
    }

    public function attributeRules(string|array|ValidationRule|Rule $rules): self
    {
        $this->arrayRules($rules);

        return $this;
    }

    public function itemRules(string|array|ValidationRule|Rule $rules): self
    {
        $this->itemRules = self::prepareRules($rules);

        return $this;
    }

    /**
     * Set the validation rules for each item in the array by name.
     */
    public function namedItemRules(array $rules): self
    {
        $this->namedItemRules = tap($rules, function ($rules) {
            foreach ($rules as $key => $rule) {
                if (is_string($rule)) {
                    $rules[$key] = explode('|', $rule);
                    continue;
                }

                if (! is_array($rules)) {
                    $rules[$key] = [$rule];
                }
            }
        });

        return $this;
    }

    protected function validateItems(string $attribute, array $items, Closure $fail): void
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

    protected function validateArrayData(string $attribute, Closure $fail, null|array $data = null, null|array $rules = null): void
    {
        $data = $data ?? $this->data;
        $rules = $rules ?? $this->attributeRules;

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
