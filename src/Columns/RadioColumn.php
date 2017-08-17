<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasName;
use Backpack\CRUD\CRUDTraits\HasLabel;

class RadioColumn extends Column
{
    use HasName, HasLabel;

    protected $type = 'radio';

    /**
     * @param $options array
     */
    public function options($options)
    {
        $this->data['options'] = $options;
    }
}
