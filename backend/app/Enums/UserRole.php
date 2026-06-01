<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Requester = 'requester';
    case Manager = 'manager';
    case Admin = 'admin';

    public function isApprover(): bool
    {
        return in_array($this, [self::Manager, self::Admin]);
    }
}
