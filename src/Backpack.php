<?php

namespace Backpack\CRUD;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class Backpack
{
    /**
     * The callback that will generate the "default" version of backpack password rule.
     *
     * @var string|array|callable|null
     */
    protected static $defaultPasswordRulesCallback;

    /**
     * Get the default configuration of backpack password rule.
     *
     * @return static
     */
    public static function passwordRulesDefault()
    {
        $password = is_callable(static::$defaultPasswordRulesCallback)
            ? call_user_func(static::$defaultPasswordRulesCallback)
            : static::$defaultPasswordRulesCallback;

        return $password instanceof Rule ? $password : Password::min(8);
    }
    
    /**
     * Set the default callback to be used for determining backpack password's default rules.
     *
     * If no arguments are passed, the default password rule configuration will be returned.
     *
     * @param  static|callable|null  $callback
     * @return static|null
     */
    public static function passwordRulesDefaults($callback = null)
    {
        if (is_null($callback)) {
            return static::passwordRulesDefault();
        }

        if (! is_callable($callback) && ! $callback instanceof Password) {
            throw new InvalidArgumentException('The given callback should be callable or an instance of '.Password::class);
        }

        static::$defaultPasswordRulesCallback = $callback;
    }
}
