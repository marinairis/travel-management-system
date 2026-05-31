<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use Illuminate\Support\Collection;

interface CityRepositoryInterface
{
    public function getAll(): Collection;

    public function getDestinations(): Collection;

    public function search(?string $query): Collection;
}
