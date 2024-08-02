<?php

namespace Backpack\CRUD\Tests\config\Models;

class UserWithTranslations extends User
{
    use Traits\HasTranslationsFake;

    public static function getDbTableSchema()
    {
        return new class
        {
            public function getColumnType($column)
            {
                return $column;
            }
        };
    }
}
