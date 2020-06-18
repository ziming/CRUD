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
     * If a developer calls a method that doesn't exist, assume they want:
     * - the CrudField object to have an attribute with that value;
     * - that field be updated inside the global CrudPanel object;.
     *
     * Eg: type('number') will set the "type" attribute to "number"
     *
     * @param  string $method     The method being called that doesn't exist.
     * @param  array $parameters  The arguments when that method was called.
     *
     * @return CrudField
     */
    public function __call($method, $parameter)
    {
        foreach($this->fields as $field) {
            $field->setAttributeValue($method, $parameter[0]);
            $field->save();
        }
        return $this;
    }
}
