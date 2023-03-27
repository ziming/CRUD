<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits\Support;

trait HasMacros
{
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
}
