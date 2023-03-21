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

    public function getMacros()
    {
        return static::$macros;
    }
}
