<?php

namespace Backpack\CRUD\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Route;
use Backpack\Basset\BassetServiceProvider;
use Backpack\CRUD\BackpackServiceProvider;

abstract class BaseTest extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Route::group([
            (array) config('backpack.base.web_middleware', 'web'),
            (array) config('backpack.base.middleware_key', 'admin'),
            'prefix'     => config('backpack.base.route_prefix', 'admin'),
        ],
            function () {
                Route::crud('users', 'Backpack\CRUD\Tests\Unit\Http\Controllers\UserCrudController');
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            BassetServiceProvider::class,
            BackpackServiceProvider::class,
        ];
    }

    // allow us to run crud panel private/protected methods like `inferFieldTypeFromDbColumnType`
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
