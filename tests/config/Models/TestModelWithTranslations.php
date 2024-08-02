<?php

namespace Backpack\CRUD\Tests\Config\Models;

class TestModelWithTranslations extends TestModel
{
    public function translationEnabledForModel()
    {
        return true;
    }

    public function getTranslations()
    {
        return ['translatableColumn' => null];
    }
}
