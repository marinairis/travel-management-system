<?php

declare(strict_types=1);

namespace App\Enums;

enum TravelType: string
{
    case Bus = 'bus';
    case Plane = 'plane';
    case Car = 'car';
    case Hotel = 'hotel';
}
