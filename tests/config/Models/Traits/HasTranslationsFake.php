<?php

namespace Backpack\CRUD\Tests\config\Models\Traits;

trait HasTranslationsFake
{
    public function translationEnabledForModel()
    {
        return true;
    }

    public function getTranslations()
    {
        return ['translatableColumn' => null];
    }

    public function isTranslatableAttribute(string $attribute)
    {
        return true;
    }
}
