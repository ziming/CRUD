<?php

namespace Backpack\CRUD\Tests\Config\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;

class TestModelWithTranslations extends TestModel
{
   public function translationEnabledForModel()
   {
        return true;
   }

   public function getTranslations()
   {
         return ['translatableColumn'];
   }
}
