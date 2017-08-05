<?php


namespace Backpack\CRUD\ColumnsTraits;

use Backpack\CRUD\Facades\CRUDPanel;

trait ManageColumns
{

    /**
     * Remove all previously set columns and add the provided ones
     * @param $columns array
     */
    public function set($columns)
    {
        CRUDPanel::setColumns($columns);
    }

    /**
     * @param $columns [string, array or multidimensional array]
     * @return \Backpack\CRUD\Facades\CRUDPanel
     */
    public function add($columns)
    {
        // if a string was passed
        if (! is_array($columns)) {
            return CRUDPanel::addColumn($columns);
        }

        if (is_array($columns) && count($columns)) {
            // it's a column as an associative array
            if (key_exists('name', $columns) || key_exists('label', $columns) ) {
                return CRUDPanel::addColumn($columns);
            }
            // we have an array of columns
            return CRUDPanel::addColumns($columns);
        }
    }

    /**
     * @param $columns [column array or array of columns]
     */
    public function remove($columns)
    {
        if (! is_array($columns)) {
            CRUDPanel::removeColumn($columns);
        } else {
            CRUDPanel::removeColumns($columns);
        }
    }
}