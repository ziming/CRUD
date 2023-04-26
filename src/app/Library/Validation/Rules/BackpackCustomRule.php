<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Database\Eloquent\Model;

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

    public array $itemRules;

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
    public function itemRules(string|array|ValidationRule $rules): self
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
     * use itemRules as an alias for make()->itemRules().
     */
    public static function __callStatic($name, $arguments)
    {
        if ($name === 'itemRules') {
            $instance = new static();
            return $instance->itemRules($arguments[0]);
        }
    }
}
