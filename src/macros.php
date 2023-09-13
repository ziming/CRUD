<?php

use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;
use Backpack\CRUD\app\Library\CrudPanel\CrudField;
use Backpack\CRUD\app\Library\Uploaders\Support\RegisterUploadEvents;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * This macro adds the ability to convert a dot.notation string into a [braket][notation] with some special
 * options that helps us in our usecases.
 *
 * - $ignore: usefull when you want to convert a laravel validator rule for nested items and you
 *   would like to ignore the `*` element from the string.
 *
 * - $keyFirst: when true, we will use the first part of the string as key and only bracket the remaining elements.
 *   eg: `address.street`
 *      - when true: `address[street]`
 *      - when false: `[address][street]`
 */
if (! Str::hasMacro('dotsToSquareBrackets')) {
    Str::macro('dotsToSquareBrackets', function ($string, $ignore = [], $keyFirst = true) {
        $stringParts = explode('.', $string);
        $result = '';

        foreach ($stringParts as $key => $part) {
            if (in_array($part, $ignore)) {
                continue;
            }
            $result .= ($key === 0 && $keyFirst) ? $part : '['.$part.']';
        }

        return $result;
    });
}
if (! CrudColumn::hasMacro('withFiles')) {
    CrudColumn::macro('withFiles', function ($uploadDefinition = [], $subfield = null, $registerUploaderEvents = true) {
        /** @var CrudField|CrudColumn $this */
        RegisterUploadEvents::handle($this, $uploadDefinition, 'withFiles', $subfield, $registerUploaderEvents);

        return $this;
    });
}

if (! CrudField::hasMacro('withFiles')) {
    CrudField::macro('withFiles', function ($uploadDefinition = [], $subfield = null, $registerUploaderEvents = true) {
        /** @var CrudField|CrudColumn $this */
        RegisterUploadEvents::handle($this, $uploadDefinition, 'withFiles', $subfield, $registerUploaderEvents);

        return $this;
    });
}

if (!CrudColumn::hasMacro('linkTo')) {
    CrudColumn::macro('linkTo', function ($route, $target = null) {

        $wrapper =  $this->attributes['wrapper'] ?? [];
        if (in_array($this->attributes['type'], ['select','select_grouped','select2','select2_grouped','select2_nested','select2_from_ajax'])) {
            $wrapper['href'] = function ($crud, $column, $entry, $related_key) use ($route) {
                return route($route, $related_key);
            };
        } else {
            $wrapper['href'] = function ($crud,$column,$entry) use ($route) {
                return url($route.$column["value"]);
            };
        }

        if ($target)
            $wrapper['target'] = $target;

        $this->wrapper($wrapper);
        return $this;
    });
}

/**
 * The route macro allows developers to generate the routes for a CrudController,
 * for all operations, using a simple syntax: Route::crud().
 *
 * It will go to the given CrudController and get the setupRoutes() method on it.
 */
if (! Route::hasMacro('crud')) {
    Route::macro('crud', function ($name, $controller) {
        // put together the route name prefix,
        // as passed to the Route::group() statements
        $routeName = '';
        if ($this->hasGroupStack()) {
            foreach ($this->getGroupStack() as $key => $groupStack) {
                if (isset($groupStack['name'])) {
                    if (is_array($groupStack['name'])) {
                        $routeName = implode('', $groupStack['name']);
                    } else {
                        $routeName = $groupStack['name'];
                    }
                }
            }
        }
        // add the name of the current entity to the route name prefix
        // the result will be the current route name (not ending in dot)
        $routeName .= $name;

        // get an instance of the controller
        if ($this->hasGroupStack()) {
            $groupStack = $this->getGroupStack();
            $groupNamespace = $groupStack && isset(end($groupStack)['namespace']) ? end($groupStack)['namespace'].'\\' : '';
        } else {
            $groupNamespace = '';
        }
        $namespacedController = $groupNamespace.$controller;
        $controllerInstance = App::make($namespacedController);

        return $controllerInstance->setupRoutes($name, $routeName, $controller);
    });
}
