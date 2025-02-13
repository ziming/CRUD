<?php

namespace Backpack\CRUD\Tests\Config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class TranslatableModel extends Model
{
    use CrudTrait;
    use HasTranslations;

    protected $fillable = [
        'title',
        'description',
    ];

    protected $translatable = [
        'title',
        'description',
    ];

    public $timestamps = false;

    protected $table = 'translatable';
}
