<?php

namespace Backpack\CRUD\Tests\Config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Star extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'stars';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['title'];

    public function starable()
    {
        return $this->morphTo();
    }

    public function identifiableAttribute()
    {
        return 'title';
    }
}
