<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Enums;

enum ButtonPositionEnum: string
{
    public const END = 'end';
    public const BEGINNING = 'beginning';

    public static function isValid($position): bool
    {
        return in_array($position, [self::END, self::BEGINNING]);
    }

    public static function getValues(): array
    {
        return [self::END, self::BEGINNING];
    }
}
