<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

class FieldGroup
{
    protected $fields;

    public function __construct(...$fields)
    {
        $this->fields = $fields;
    }

    // -------------
    // MAGIC METHODS
    // -------------

    /**
     * We foward any call into FieldGroup class to the Field class once per defined field.
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return FieldGroup
     */
    public function __call($method, $parameter)
    {
        foreach ($this->fields as $field) {
            $field->{$method}($parameter[0]);
        }

        return $this;
    }
}
