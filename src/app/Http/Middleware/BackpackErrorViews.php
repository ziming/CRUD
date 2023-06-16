<?php

namespace Backpack\CRUD\app\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class BackpackErrorViews
{
    public function handle($request, Closure $next)
    {
        if (! Str::startsWith($request->path(), config('backpack.base.route_prefix'))) {
            return $next($request);
        }

        [$themeNamespace, $themeFallbackNamespace] = $this->getThemeNamespaces();

        $viewFinderHints = app('view')->getFinder()->getHints();

        // here we are going to generate the paths array containing:
        // - theme paths
        // - fallback theme paths
        // - ui path
        $themeErrorPaths = $viewFinderHints[$themeNamespace] ?? [];
        $themeErrorPaths = $themeNamespace === $themeFallbackNamespace ? $themeErrorPaths :
            array_merge($viewFinderHints[$themeFallbackNamespace] ?? [], $themeErrorPaths);
        $uiErrorPaths = [base_path('vendor/backpack/crud/src/resources/views/ui')];
        $themeErrorPaths = array_merge($themeErrorPaths, $uiErrorPaths);

        // merge the paths array with the view.paths defined in the application
        app('config')->set('view.paths', array_merge($themeErrorPaths, config('view.paths', [])));

        return $next($request);
    }

    private function getThemeNamespaces(): array
    {
        return [
            $this->getNamespaceFrom(config('backpack.ui.view_namespace')),
            $this->getNamespaceFrom(config('backpack.ui.view_namespace_fallback')),
        ];
    }

    private function getNamespaceFrom(string $rawNamespace): string
    {
        // the namespace always ends with `::` or `.`
        return Str::endsWith($rawNamespace, '::') ? substr($rawNamespace, 0, -2) : substr($rawNamespace, 0, -1);
    }
}
