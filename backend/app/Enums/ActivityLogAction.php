<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivityLogAction: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case StatusChange = 'status_change';
}
