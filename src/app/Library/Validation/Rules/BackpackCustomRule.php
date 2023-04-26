<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * @method static static itemRules()
 */
abstract class BackpackCustomRule implements ValidationRule, DataAwareRule, ValidatorAwareRule
{
    /**
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    protected array $data;

    public array $attributeRules = [];

    public ?Model $entry;

    public bool $implicit = true;

    public function __construct()
    {
        $entry = CrudPanelFacade::getCurrentEntry();
        $this->entry = $entry !== false ? $entry : null;
    }

    public static function make(): self
    {
        $instance = new static();

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
        // is the extending class reponsability the implementation of the validation logic
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
     * Set the rules that apply to the item sent in request.
     */
    public function attributeRules(string|array|ValidationRule $rules): self
    {
        $this->attributeRules = self::prepareRules($rules);

        return $this;
    }

    public function validateAttributeRules(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = Validator::make([$attribute => $value], [
            $attribute => $this->getAttributeRules(),
        ], $this->validator->customMessages, $this->validator->customAttributes);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages()[$attribute] as $message) {
                $fail($message)->translate();
            }
        }
    }

    public function getAttributeRules(): array
    {
        return tap($this->attributeRules, function ($rule) {
            if (is_a($rule, BackpackCustomRule::class, true)) {
                $rule = $rule->getAttributeRules();
            }

            return $rule;
        });
    }

    protected static function prepareRules($rules)
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        if (! is_array($rules)) {
            $rules = [$rules];
        }

        return $rules;
    }
}
