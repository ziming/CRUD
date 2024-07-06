<?php

namespace Backpack\CRUD\app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PublishHeaderMetas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:publish-header-metas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes the header metas and assets.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appName = $this->ask('Whats the application name ?', 'Backpack');
        $appUrl = $this->ask('Whats the application url ?', config('app.url'));
        $backpackPrefix = config('backpack.base.route_prefix');
        $appColor = $this->ask('Whats the application color ?', '#605ca8');
        $pathPrefix = $this->ask('Where should icon files be published relative to public folder?');

        if ($pathPrefix) {
            $pathPrefix = Str::finish($pathPrefix, '/');
        }

        $fileToPublish = [
            public_path($pathPrefix.'manifest.json') => __DIR__.'/../../../resources/stubs/manifest.stub',
            public_path($pathPrefix.'site.webmanifest') => __DIR__.'/../../../resources/stubs/manifest.stub',
            resource_path('views/vendor/backpack/ui/inc/header_metas.blade.php') => __DIR__.'/../../../resources/stubs/header_metas.stub',
            public_path($pathPrefix.'android-chrome-192x192.png'),
            public_path($pathPrefix.'android-chrome-512x512.png'),
            public_path($pathPrefix.'apple-touch-icon.png'),
            public_path($pathPrefix.'favicon-16x16.png'),
            public_path($pathPrefix.'favicon-32x32.png'),
            public_path($pathPrefix.'favicon.ico'),
            public_path($pathPrefix.'safari-pinned-tab.svg'),
        ];

        foreach ($fileToPublish as $destination => $stub) {
            if (! is_string($destination)) {
                $destination = $stub;
                $stub = null;
            }

            if (File::exists($destination)) {
                $this->comment("The file {$destination} already exists. Skipping.");

                continue;
            }

            if (! File::isDirectory(dirname($destination))) {
                File::makeDirectory(dirname($destination), 0755, true);
            }

            if (! $stub) {
                File::copy(__DIR__.'/../../../public/'.basename($destination), $destination);
                $this->info("File {$destination} published.");
                continue;
            }

            $stub = File::get($stub);

            $stub = str_replace('__APP_NAME__', $appName, $stub);
            $stub = str_replace('__APP_URL__', $appUrl, $stub);
            $stub = str_replace('__APP_COLOR__', $appColor, $stub);
            $stub = str_replace('__BACKPACK_PREFIX__', $backpackPrefix, $stub);
            $stub = str_replace('__PATH_PREFIX__', $pathPrefix, $stub);

            File::put($destination, $stub);

            $this->info("File {$destination} published.");
        }

        $this->info('Metas and assets published successfully.');
    }
}
