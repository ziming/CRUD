<?php

namespace Backpack\CRUD;

use Backpack\Basset\Facades\Basset;
use Backpack\CRUD\app\Http\Middleware\ThrottlePasswordRecovery;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\Database\DatabaseSchema;
use Backpack\CRUD\app\Library\Uploaders\Support\UploadersRepository;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class BackpackServiceProvider extends ServiceProvider
{
    use Stats;

    protected $commands = [
        \Backpack\CRUD\app\Console\Commands\Install::class,
        \Backpack\CRUD\app\Console\Commands\AddMenuContent::class,
        \Backpack\CRUD\app\Console\Commands\AddCustomRouteContent::class,
        \Backpack\CRUD\app\Console\Commands\PublishAssets::class,
        \Backpack\CRUD\app\Console\Commands\Version::class,
        \Backpack\CRUD\app\Console\Commands\CreateUser::class,
        \Backpack\CRUD\app\Console\Commands\PublishBackpackMiddleware::class,
        \Backpack\CRUD\app\Console\Commands\PublishView::class,
        \Backpack\CRUD\app\Console\Commands\Addons\RequireDevTools::class,
        \Backpack\CRUD\app\Console\Commands\Addons\RequireEditableColumns::class,
        \Backpack\CRUD\app\Console\Commands\Addons\RequirePro::class,
        \Backpack\CRUD\app\Console\Commands\Fix::class,
    ];

    // Indicates if loading of the provider is deferred.
    protected $defer = false;

    // Where the route file lives, both inside the package and in the app (if overwritten).
    public $routeFilePath = '/routes/backpack/base.php';

    // Where custom routes can be written, and will be registered by Backpack.
    public $customRoutesFilePath = '/routes/backpack/custom.php';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadViewsWithFallbacks('crud');
        $this->loadViewsWithFallbacks('ui', 'backpack.ui');
        $this->loadTranslationsFrom(realpath(__DIR__.'/resources/lang'), 'backpack');
        $this->loadConfigs();
        $this->registerMiddlewareGroup($this->app->router);
        $this->setupRoutes($this->app->router);
        $this->setupCustomRoutes($this->app->router);
        $this->publishFiles();
        $this->sendUsageStats();

        Basset::addViewPath(realpath(__DIR__.'/resources/views'));
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // load the macros
        include_once __DIR__.'/macros.php';

        // Bind the CrudPanel object to Laravel's service container
        $this->app->scoped('crud', function ($app) {
            return new CrudPanel();
        });

        $this->app->scoped('DatabaseSchema', function ($app) {
            return new DatabaseSchema();
        });

        $this->app->singleton('BackpackViewNamespaces', function ($app) {
            return new ViewNamespaces();
        });

        // Bind the widgets collection object to Laravel's service container
        $this->app->singleton('widgets', function ($app) {
            return new Collection();
        });

        $this->app->scoped('UploadersRepository', function ($app) {
            return new UploadersRepository();
        });

        // register the helper functions
        $this->loadHelpers();

        // register the artisan commands
        $this->commands($this->commands);
    }

    public function registerMiddlewareGroup(Router $router)
    {
        $middleware_key = config('backpack.base.middleware_key');
        $middleware_class = config('backpack.base.middleware_class');

        if (! is_array($middleware_class)) {
            $router->pushMiddlewareToGroup($middleware_key, $middleware_class);

            return;
        }

        foreach ($middleware_class as $middleware_class) {
            $router->pushMiddlewareToGroup($middleware_key, $middleware_class);
        }

        // register internal backpack middleware for throttling the password recovery functionality
        // but only if functionality is enabled by developer in config
        if (config('backpack.base.setup_password_recovery_routes')) {
            $router->aliasMiddleware('backpack.throttle.password.recovery', ThrottlePasswordRecovery::class);
        }
    }

    public function publishFiles()
    {
        $error_views = [__DIR__.'/resources/error_views' => resource_path('views/errors')];
        $backpack_views = [__DIR__.'/resources/views' => resource_path('views/vendor/backpack')];
        $backpack_public_assets = [__DIR__.'/public' => public_path()];
        $backpack_lang_files = [__DIR__.'/resources/lang' => app()->langPath().'/vendor/backpack'];
        $backpack_config_files = [__DIR__.'/config' => config_path()];

        // sidebar content views, which are the only views most people need to overwrite
        $backpack_menu_contents_view = [
            __DIR__.'/resources/views/ui/inc/menu_items.blade.php' => resource_path('views/vendor/backpack/ui/inc/menu_items.blade.php'),
        ];
        $backpack_custom_routes_file = [__DIR__.$this->customRoutesFilePath => base_path($this->customRoutesFilePath)];

        // calculate the path from current directory to get the vendor path
        $vendorPath = dirname(__DIR__, 3);
        $gravatar_assets = [$vendorPath.'/creativeorange/gravatar/config' => config_path()];

        // establish the minimum amount of files that need to be published, for Backpack to work; there are the files that will be published by the install command
        $minimum = array_merge(
            // $backpack_views,
            // $backpack_lang_files,
            $error_views,
            $backpack_public_assets,
            $backpack_config_files,
            $backpack_menu_contents_view,
            $backpack_custom_routes_file,
            $gravatar_assets
        );

        // register all possible publish commands and assign tags to each
        $this->publishes($backpack_config_files, 'config');
        $this->publishes($backpack_lang_files, 'lang');
        $this->publishes($backpack_views, 'views');
        $this->publishes($backpack_menu_contents_view, 'menu_contents');
        $this->publishes($error_views, 'errors');
        $this->publishes($backpack_public_assets, 'public');
        $this->publishes($backpack_custom_routes_file, 'custom_routes');
        $this->publishes($gravatar_assets, 'gravatar');
        $this->publishes($minimum, 'minimum');
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        // by default, use the routes file provided in vendor
        $routeFilePathInUse = __DIR__.$this->routeFilePath;

        // but if there's a file with the same name in routes/backpack, use that one
        if (file_exists(base_path().$this->routeFilePath)) {
            $routeFilePathInUse = base_path().$this->routeFilePath;
        }

        $this->loadRoutesFrom($routeFilePathInUse);
    }

    /**
     * Load custom routes file.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupCustomRoutes(Router $router)
    {
        // if the custom routes file is published, register its routes
        if (file_exists(base_path().$this->customRoutesFilePath)) {
            $this->loadRoutesFrom(base_path().$this->customRoutesFilePath);
        }
    }

    public function loadViewsWithFallbacks($dir, $namespace = null)
    {
        $customFolder = resource_path('views/vendor/backpack/'.$dir);
        $vendorFolder = realpath(__DIR__.'/resources/views/'.$dir);
        $namespace = $namespace ?? $dir;

        // first the published/overwritten views (in case they have any changes)
        if (file_exists($customFolder)) {
            $this->loadViewsFrom($customFolder, $namespace);
        }
        // then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom($vendorFolder, $namespace);
    }

    protected function mergeConfigsFromDirectory($dir)
    {
        $configs = scandir(__DIR__."/config/backpack/$dir/");
        $configs = array_diff($configs, ['.', '..']);

        if (! count($configs)) {
            return;
        }

        foreach ($configs as $configFile) {
            $this->mergeConfigFrom(
                __DIR__."/config/backpack/$dir/$configFile",
                "backpack.$dir.".substr($configFile, 0, strrpos($configFile, '.'))
            );
        }
    }

    public function loadConfigs()
    {
        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(__DIR__.'/config/backpack/crud.php', 'backpack.crud');
        $this->mergeConfigFrom(__DIR__.'/config/backpack/base.php', 'backpack.base');
        $this->mergeConfigFrom(__DIR__.'/config/backpack/ui.php', 'backpack.ui');
        $this->mergeConfigsFromDirectory('operations');

        // add the root disk to filesystem configuration
        app()->config['filesystems.disks.'.config('backpack.base.root_disk_name')] = [
            'driver' => 'local',
            'root'   => base_path(),
        ];

        /*
         * Backpack login differs from the standard Laravel login.
         * As such, Backpack uses its own authentication provider, password broker and guard.
         *
         * THe process below adds those configuration values on top of whatever is in config/auth.php.
         * Developers can overwrite the backpack provider, password broker or guard by adding a
         * provider/broker/guard with the "backpack" name inside their config/auth.php file.
         * Or they can use another provider/broker/guard entirely, by changing the corresponding
         * value inside config/backpack/base.php
         */

        // add the backpack_users authentication provider to the configuration
        app()->config['auth.providers'] = app()->config['auth.providers'] +
            [
                'backpack' => [
                    'driver' => 'eloquent',
                    'model' => config('backpack.base.user_model_fqn'),
                ],
            ];

        // add the backpack_users password broker to the configuration
        $laravelAuthPasswordBrokers = app()->config['auth.passwords'];

        $backpackPasswordBrokerTable = config('backpack.base.password_resets_table') ??
                                        config('auth.passwords.users.table') ??
                                        current($laravelAuthPasswordBrokers)['table'];

        app()->config['auth.passwords'] = $laravelAuthPasswordBrokers +
        [
            'backpack' => [
                'provider'  => 'backpack',
                'table'     => $backpackPasswordBrokerTable,
                'expire'    => config('backpack.base.password_recovery_token_expiration', 60),
                'throttle'  => config('backpack.base.password_recovery_throttle_notifications'),
            ],
        ];

        // add the backpack_users guard to the configuration
        app()->config['auth.guards'] = app()->config['auth.guards'] +
            [
                'backpack' => [
                    'driver' => 'session',
                    'provider' => 'backpack',
                ],
            ];
    }

    /**
     * Load the Backpack helper methods, for convenience.
     */
    public function loadHelpers()
    {
        require_once __DIR__.'/helpers.php';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['crud', 'widgets', 'BackpackViewNamespaces', 'DatabaseSchema', 'UploadersRepository'];
    }
}
