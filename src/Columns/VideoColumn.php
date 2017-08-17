<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasName;
use Backpack\CRUD\CRUDTraits\HasLabel;

class VideoColumn extends Column
{
    use HasName, HasLabel;

    protected $type = 'video';
}
