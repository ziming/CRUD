<?php

namespace Backpack\CRUD\Tests\Config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Bang extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'bangs';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['name'];

    public function accountDetails()
    {
        return $this->belongsToMany('Backpack\CRUD\Tests\config\Models\AccountDetails');
    }

    public function identifiableAttribute()
    {
        return 'name';
    }
}
