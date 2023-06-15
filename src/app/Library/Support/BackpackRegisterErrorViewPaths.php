<?php

namespace Backpack\CRUD\app\Library\Support;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class BackpackRegisterErrorViewPaths
{
    /**
     * Register the error view paths.
     *
     * @return void
     */
    public function __invoke()
    {
        $themeNamespace = substr(config('backpack.ui.view_namespace'), 0, -2);
        $themeFallbackNamespace = substr(config('backpack.ui.view_namespace_fallback'), 0, -2);
        $viewFinderHints = app('view')->getFinder()->getHints();

        $themeErrorPaths = $viewFinderHints[$themeNamespace] ?? [];
        $themeErrorPaths = $themeNamespace === $themeFallbackNamespace ? $themeErrorPaths : 
            array_merge(($viewFinderHints[$themeFallbackNamespace] ?? []), $themeErrorPaths);

        $appErrorPaths = $viewFinderHints['errors'];

        $themeErrorPaths = array_merge($themeErrorPaths, $appErrorPaths);
        
        View::replaceNamespace('errors', collect($themeErrorPaths)->map(function ($path) {
            return Str::of($path)->finish('/')->value().'errors';
        })->all());
    }
}
