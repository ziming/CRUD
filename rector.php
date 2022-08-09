<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Laravel\Set\LaravelSetList;
use Rector\Nette\Set\NetteSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {

    // If you want to speed things up
    // $rectorConfig->parallel();
    
    // you can instruct rector to only run against the changed files too, but I have forgotten that rule for now.
    
    // is your PHP version different from the one you refactor to? [default: your PHP version], uses PHP_VERSION_ID format
    $rectorConfig->phpVersion(PhpVersion::PHP_73);

    $rectorConfig->paths([
        __DIR__.'/src',
    ]);

    // How to ignore or skip specific paths or files
    // https://github.com/rectorphp/rector/blob/HEAD//docs/how_to_ignore_rule_or_paths.md

    // register a single rule
    // full list here: https://github.com/rectorphp/rector/blob/HEAD//docs/rector_rules_overview.md
    // $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // Path to PHPStan with extensions, that PHPStan in Rector uses to determine types
    // $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');

    // Static Reflection and Autoload
    // https://github.com/rectorphp/rector/blob/HEAD//docs/static_reflection_and_autoload.md

    // use short version of import names rather than the fully qualified paths
    // https://github.com/rectorphp/rector/blob/main/docs/auto_import_names.md
    $rectorConfig->importNames();

    // define sets of rules
    $rectorConfig->sets([
        // DowngradeLevelSetList::DOWN_TO_PHP_73,
        // LevelSetList::UP_TO_PHP_81,
        // LaravelSetList::LARAVEL_90,
        // SetList::CODE_QUALITY,
        // SetList::DEAD_CODE, // remove unused code
        // SetList::PRIVATIZATION, // make visibility more private, make some class and variables final.etc
        // SetList::NAMING,
        SetList::TYPE_DECLARATION, // declare those types natively or in docblock for older PHP versions
        // SetList::EARLY_RETURN,
        // SetList::TYPE_DECLARATION_STRICT, // not too sure of the difference between this and TYPE_DECLARATION
        // NetteSetList::NETTE_UTILS_CODE_QUALITY,
        // PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        // SetList::CODING_STYLE, // u prob use pint instead for code style.
    ]);
};
