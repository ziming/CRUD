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
    protected $description = 'Publishes the header metas and favicon assets.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appName = $this->ask('What is the application name ?', config('app.name').' Backoffice');
        $backpackPrefix = config('backpack.base.route_prefix');
        $appColor = $this->ask('What is the application color ?', '#161c2d');
        $pathPrefix = $this->ask('Where should icon files be published relative to public folder?');

        $pathPrefix = Str::start(Str::finish($pathPrefix ?? '', '/'), '/');

        // laravel adds a dummy favicon with 0 bytes. we need to remove it otherwise our script would skip publishing the favicon on new Laravel installations.
        // we will check the favicon file size, to make sure it's not a "valid" favicon. we will only delete the favicon if it has 0 bytes in size.
        $this->checkIfFaviconIsLaravelDefault($pathPrefix);

        $stubPath = __DIR__.'/../../../resources/stubs/';

        $filesToPublish = [
            public_path($pathPrefix.'site.webmanifest') => $stubPath.'manifest.stub',
            resource_path('views/vendor/backpack/ui/inc/header_metas.blade.php') => $stubPath.'header_metas.stub',
            public_path($pathPrefix.'browserconfig.xml') => $stubPath.'browserconfig.stub',
            public_path($pathPrefix.'android-chrome-192x192.png') => __DIR__.'/../../../public/android-chrome-192x192.png',
            public_path($pathPrefix.'android-chrome-192x192.png'),
            public_path($pathPrefix.'android-chrome-512x512.png'),
            public_path($pathPrefix.'apple-touch-icon.png'),
            public_path($pathPrefix.'favicon-16x16.png'),
            public_path($pathPrefix.'favicon-32x32.png'),
            public_path($pathPrefix.'favicon.ico'),
            public_path($pathPrefix.'safari-pinned-tab.svg'),
            public_path($pathPrefix.'mstile-70x70.png'),
            public_path($pathPrefix.'mstile-144x144.png'),
            public_path($pathPrefix.'mstile-150x150.png'),
            public_path($pathPrefix.'mstile-310x150.png'),
            public_path($pathPrefix.'mstile-310x310.png'),
        ];

        foreach ($filesToPublish as $destination => $stub) {
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
            $stub = str_replace('__APP_COLOR__', $appColor, $stub);
            $stub = str_replace('__BACKPACK_PREFIX__', $backpackPrefix, $stub);
            $stub = str_replace('__PATH_PREFIX__', $pathPrefix, $stub);

            File::put($destination, $stub);

            $this->info("File {$destination} published.");
        }

        $this->comment('[DONE] Metas and favicon assets published successfully.');
    }

    private function checkIfFaviconIsLaravelDefault(string $path)
    {
        if (File::exists(public_path($path.'favicon.ico'))) {
            // check the file size. if it's 0 it's the laravel dummy favicon, remove it.
            if (filesize(public_path($path.'favicon.ico')) === 0) {
                File::delete(public_path($path.'favicon.ico'));
                $this->comment('[INFO] We deleted the Laravel dummy favicon. Publishing assets now.');
            }
        }
    }
}
