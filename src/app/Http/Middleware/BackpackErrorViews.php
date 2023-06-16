<?php

namespace Backpack\CRUD\app\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class BackpackErrorViews
{
    public function handle($request, Closure $next)
    {
        if (Str::startsWith($request->path(), config('backpack.base.route_prefix'))) {
            $themeNamespace = substr(config('backpack.ui.view_namespace'), 0, -2);
            $themeFallbackNamespace = substr(config('backpack.ui.view_namespace_fallback'), 0, -2);
            $viewFinderHints = app('view')->getFinder()->getHints();

            $themeErrorPaths = $viewFinderHints[$themeNamespace] ?? [];
            $themeErrorPaths = $themeNamespace === $themeFallbackNamespace ? $themeErrorPaths :
                array_merge($viewFinderHints[$themeFallbackNamespace] ?? [], $themeErrorPaths);
            $uiErrorPaths = [base_path('vendor/backpack/crud/src/resources/views/ui')];
            $themeErrorPaths = array_merge($themeErrorPaths, $uiErrorPaths);

            app('config')->set('view.paths', array_merge($themeErrorPaths, config('view.paths', [])));
        }

        return $next($request);
    }
}
