const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Backpack maintainers use mix to:
 | - install and update CSS and JS assets;
 | - copy everything that needs to be published into src/public
 |
 | All JS will be bundled into one file (see bundle.js).
 |
 | How to use (for maintainers only):
 | - cd vendor/backpack/crud
 | - npm install
 | - npm run prod
 | (this will also publish the assets for you to test, so no need to do that too)
 */

// merge all needed JS into a big bundle file
mix.js('src/resources/assets/js/bundle.js', 'src/public/packages/backpack/base/js/');

// merge all needed CSS into a big bundle file
mix.sass('src/resources/assets/scss/bundle.scss', 'src/public/packages/backpack/base/css/')
	.options({
      processCssUrls: false
    });

// merge all needed CSS into a big bundle file (this time with a blue primary color)
mix.sass('src/resources/assets/scss/blue-bundle.scss', 'src/public/packages/backpack/base/css/')
	.options({
      processCssUrls: false
    });

// copy the Backstrap CSS
mix.copy('node_modules/@digitallyhappy/backstrap/dist/css', 'src/public/packages/@digitallyhappy/backstrap/css');

// FOR MAINTAINERS
// copy asset files from Base's public folder the main app's public folder
// so that you don't have to publish the assets with artisan to test them
mix.copyDirectory('src/public', '../../../public')