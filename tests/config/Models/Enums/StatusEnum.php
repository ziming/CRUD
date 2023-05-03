<?php

namespace Backpack\CRUD\Tests\Config\Models\Enums;

enum StatusEnum: string
{
    case DRAFT = 'drafted';
    case PUBLISHED = 'publish';
}
