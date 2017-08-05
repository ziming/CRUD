<?php


namespace Backpack\CRUD\Columns;

abstract class Column
{
    protected $type;
    protected $data = [];

    /**
     * Column constructor.
     * @param null $data
     */
    function __construct($data = null)
    {
        if (is_array($data)) {
            $this->data = $data;
        }
    }

    /**
     * Generate the data for CrudPanel
     * @return array this colums array representation
     */
    public function toArray()
    {
        return array_merge($this->data, ['type' => $this->type]);
    }
}