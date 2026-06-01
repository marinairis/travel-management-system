<?php

declare(strict_types=1);

namespace App\Interfaces\Services;

interface IbgeServiceInterface
{
    public function getCity(): array;

    public function searchCities(?string $query = null): mixed;
}
