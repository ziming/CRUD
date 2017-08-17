<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasName;
use Backpack\CRUD\CRUDTraits\HasLabel;

class ArrayCountColumn extends Column
{
    use HasName, HasLabel;

    protected $type = 'array_count';
}
