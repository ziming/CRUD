<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class ValidArray extends BackpackCustomRule
{
    public array $arrayRules = [];

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
     * Set the validation rules for the items, by name. Eg: 'author.name' => 'required'.
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
        if (! empty($this->namedItemRules)) {
            $this->validateNamedItemRules($attribute, $items, $fail);
        }

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

    /**
     * When developer provides named rules for the items, for example: 'repeatable' => [ValidArray::make()->ruleName('required')].
     *
     * @param  stromg  $attribute
     * @param  array  $items
     * @param  Closure  $fail
     * @return void
     */
    private function validateNamedItemRules($attribute, $items, $fail)
    {
        $this->namedItemRules = array_combine(array_map(function ($ruleKey) use ($attribute) {
            return $attribute.'.*.'.$ruleKey;
        }, array_keys($this->namedItemRules)), $this->namedItemRules);

        array_walk($this->namedItemRules, function (&$value, $key) {
            if (is_array($value)) {
                $rules = [];
                foreach ($value as $rule) {
                    if (is_a($rule, get_class($this), true)) {
                        $validArrayRules = $rule->itemRules;
                        if (is_array($validArrayRules)) {
                            foreach ($validArrayRules as $validArrayRule) {
                                // dd($validArrayRule);
                            }
                            $rules = array_merge($rules, $validArrayRules);
                            continue;
                        }
                        $rules[] = $validArrayRules;
                        continue;
                    }
                    $rules[] = $rule;
                }
                $value = $rules;
            }
        });
        //dd($this->namedItemRules, $this->itemRules);
        $this->validateArrayData($attribute, $fail, $items, $this->namedItemRules);
    }

    public function __call($method, $arguments)
    {
        // if method starts with `rule` eg: ruleName, extract the input name and add it to the array of rules
        if (Str::startsWith($method, 'rule')) {
            $argument = Str::snake(Str::replaceFirst('rule', '', $method));
            $this->namedItemRules[$argument] = $arguments[0];

            return $this;
        }

        return $this->{$method}(...$arguments);
    }
}
