<?php

namespace Backpack\CRUD\Tests\config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;

class TestModel extends \Illuminate\Database\Eloquent\Model
{
    use CrudTrait;
}
