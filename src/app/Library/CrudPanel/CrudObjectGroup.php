<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

class CrudObjectGroup
{
    protected $objects;

    /**
     * Add CrudObjects (fields, columns etc) to the group.
     */
    public function __construct(...$objects)
    {
        if (is_array($objects[0])) {
            $objects = $objects[0];
        }

        $this->objects = $objects;
    }

    // -------------
    // MAGIC METHODS
    // -------------

    /**
     * We forward any call to the corresponding class passed by developer (Field, Columns, Filters etc ..).
     */
    public function __call(string $method, array $parameter)
    {
        foreach ($this->objects as $object) {
            $object->{$method}($parameter[0]);
        }

        return $this;
    }
}
