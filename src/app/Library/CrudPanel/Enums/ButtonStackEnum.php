<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Enums;

enum ButtonStackEnum: string
{
    public const TOP = 'top';

    public const LINE = 'line';

    public const BOTTOM = 'bottom';

    public const HIDDEN = 'hidden';

    public static function isValid($stack): bool
    {
        return in_array($stack, [self::TOP, self::LINE, self::BOTTOM, self::HIDDEN]);
    }

    public static function getValues(): array
    {
        return [self::TOP, self::LINE, self::BOTTOM, self::HIDDEN];
    }
}
