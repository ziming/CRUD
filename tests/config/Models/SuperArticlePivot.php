<?php

namespace Backpack\CRUD\Tests\Config\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SuperArticlePivot extends Pivot
{
    protected $table = 'articles_user';
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
