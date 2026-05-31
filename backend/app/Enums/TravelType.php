<?php

declare(strict_types=1);

namespace App\Enums;

enum TravelType: string
{
    case Bus   = 'onibus';
    case Plane = 'aereo';
    case Car   = 'carro';
    case Hotel = 'hotel';
}
