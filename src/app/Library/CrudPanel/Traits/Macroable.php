<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;
use Backpack\CRUD\app\Library\CrudPanel\CrudField;
use Illuminate\Support\Traits\Macroable as IlluminateMacroable;

trait Macroable
{
    use IlluminateMacroable {
        IlluminateMacroable::macro as parentMacro;
    }

    /**
     * In addition to registering the macro, throw an error if the method already exists on the object
     * so the developer knows why their macro is not being registered.
     *
     * @param  string  $name
     * @param  object|callable  $macro
     * @return void
     */
    public static function macro($name, $macro)
    {
        if (method_exists(new static(), $name)) {
            abort(500, "Cannot register '$name' macro. '$name()' already exists on ".get_called_class());
        }

        static::parentMacro($name, $macro);
    }

    /**
     * Calls the macros registered for the given macroable attributes.
     *
     * @param  CrudField|CrudColumn  $macroable
     * @return void
     */
    private function callRegisteredAttributeMacros(CrudField|CrudColumn $macroable)
    {
        $macros = $macroable->getMacros();
        $attributes = $macroable->getAttributes();

        foreach (array_keys($macros) as $macro) {
            if (isset($attributes[$macro])) {
                $macroable->{$macro}($attributes[$macro]);
            }
        }
    }
}
