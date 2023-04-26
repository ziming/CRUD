<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class ValidNestedArray extends ValidArray
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
        $value ??= [];
        if (! is_array($value)) {
            try {
                $value = json_decode($value, true);
            } catch (\Exception $e) {
                $fail('Unable to determine the value type.');

                return;
            }
        }

        $this->validateArrayData($attribute, $fail, $value);

        $this->validateNestedItemRules($attribute, $value, $fail);
    }

    private function validateNestedItemRules(string $attribute, array $items, Closure $fail): void
    {
        $this->namedItemRules = array_combine(array_map(function ($ruleKey) use ($attribute) {
            return $attribute.'.*.'.$ruleKey;
        }, array_keys($this->namedItemRules)), $this->namedItemRules);

        array_walk($this->namedItemRules, function (&$ruleValue, $key) {
            if (is_array($ruleValue)) {
                $rules = [];
                foreach ($ruleValue as $rule) {
                    if (is_a($rule, ValidArray::class, true)) {
                        $validArrayRules = $rule->itemRules;
                        if (is_array($validArrayRules)) {
                            $rules = array_merge($rules, $validArrayRules);
                            continue;
                        }
                        $rules[] = $validArrayRules;
                        continue;
                    }
                    $rules[] = $rule;
                }
                $ruleValue = $rules;
            }
        });

        $validator = Validator::make([$attribute => $items], $this->namedItemRules, $this->validator->customMessages, $this->validator->customAttributes);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $message) {
                foreach ($message as $message) {
                    $fail($message)->translate();
                }
            }
        }
    }

    public function itemRules(string|array|ValidationRule|Rule $rules): self
    {
        $this->namedItemRules($rules);

        return $this;
    }
}
