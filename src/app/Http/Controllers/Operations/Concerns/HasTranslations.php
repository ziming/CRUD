<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations\Concerns;

if(!method_exists(__CLASS__, 'setupTranslatorInstance')) {
    trait HasTranslations
    {
        public function setupTranslatorInstance()
        {
            if(! method_exists($this->crud->model, 'translationEnabledForModel') || ! class_exists('Spatie\Translatable\Translatable')) {
                return;
            }

            if(app('crud')->getOperationSetting('useFallbackLocale')) {
                app(\Spatie\Translatable\Translatable::class)->fallback(
                    fallbackAny: true, 
                );
                return;
            }
        }
    }
}else{
    trait HasTranslations
    {
    }
}