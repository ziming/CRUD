<?php

namespace Backpack\CRUD\app\Library\Support;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Exceptions\RegisterErrorViewPaths;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class BackpackExceptionHandler extends Handler
{
    protected function registerErrorViewPaths()
    {
        // Display the errors using the admin theme template. This invokable class return the error paths
        // in the following order:
        // 1 - userland/resources/theme-xxx/errors
        // 2 - vendor/theme-xxx/resources/errors 
        // 3 - vendor/backpack/ui/errors
        if (backpack_user() && Str::startsWith(Request::path(), config('backpack.base.route_prefix'))) {
            (new BackpackRegisterErrorViewPaths)();
        }else{
            (new RegisterErrorViewPaths)();
        }
    }
}
