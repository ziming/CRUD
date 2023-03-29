<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits\Support;

use Illuminate\Support\Traits\Macroable;

trait MacroableWithAttributes
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * Get the registered macros.
     *
     * @var array
     */
    public function getMacros()
    {
        return static::$macros;
    }

    /**
     * Call the macros registered for the given macroable attributes.
     *
     * @return void
     */
    public function callRegisteredAttributeMacros()
    {
        $macros = $this->getMacros();
        $attributes = $this->getAttributes();

        foreach (array_keys($macros) as $macro) {
            if (isset($attributes[$macro])) {
                $this->{$macro}($attributes[$macro]);

                continue;
            }
            if (isset($attributes['subfields'])) {
                foreach ($attributes['subfields'] as $subfield) {
                    if (isset($subfield[$macro])) {
                        $config = ! is_array($subfield[$macro]) ? [] : $subfield[$macro];
                        $this->{$macro}($config, $subfield);
                    }
                }
            }
        }
    }
}
