<?php

namespace Backpack\CRUD\Tests\Config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;

class TestModel extends \Illuminate\Database\Eloquent\Model
{
    use CrudTrait;

    public function buttonModelFunction()
    {
        return 'model function button test';
    }
}
