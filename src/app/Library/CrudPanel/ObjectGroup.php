<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

class ObjectGroup
{
    protected $objects;

    public function __construct(...$objects)
    {
        $this->objects = $objects;
    }

    // -------------
    // MAGIC METHODS
    // -------------

    /**
     * We foward any call to the corresponding class passed by developer (Field, Columns, Filters etc ..).
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return ObjectGroup
     */
    public function __call($method, $parameter)
    {
        foreach ($this->objects as $object) {
            $object->{$method}($parameter[0]);
        }

        return $this;
    }
}
