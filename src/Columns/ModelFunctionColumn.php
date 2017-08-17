<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasLabel;
use Backpack\CRUD\CRUDTraits\HasFunctionName;

class ModelFunctionColumn extends Column
{
    use HasLabel, HasFunctionName;

    protected $type = 'model_function';
}
