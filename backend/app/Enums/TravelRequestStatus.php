<?php

declare(strict_types=1);

namespace App\Enums;

enum TravelRequestStatus: string
{
    case Requested = 'requested';
    case Approved  = 'approved';
    case Cancelled = 'cancelled';
    case Expired   = 'expired';

    public function isOpen(): bool
    {
        return in_array($this, [self::Requested, self::Approved]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Cancelled, self::Expired]);
    }

    public function canBeApproved(): bool
    {
        return $this === self::Requested;
    }
}
