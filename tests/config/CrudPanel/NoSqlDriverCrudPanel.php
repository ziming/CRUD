<?php

namespace Backpack\CRUD\Tests\config\CrudPanel;

class NoSqlDriverCrudPanel extends \Backpack\CRUD\app\Library\CrudPanel\CrudPanel
{
    public function getSqlDriverList()
    {
        return [];
    }
}
