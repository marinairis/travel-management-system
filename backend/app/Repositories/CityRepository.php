<?php

namespace App\Repositories;

use App\Services\IbgeService;
use App\DTOs\CityDTO;
use Illuminate\Support\Collection;

class CityRepository
{
  public function __construct(private IbgeService $ibgeService) {}

  public function getAll(): Collection
  {
    return $this->formatCities($this->ibgeService->getCity());
  }

  public function search(string|null $query = null): Collection
  {
    $cities = $query
      ? $this->ibgeService->searchCities($query)
      : $this->ibgeService->getCity();

    return $this->formatCities($cities);
  }

  private function formatCities($cities): Collection
  {
    return collect($cities)->map(function ($city) {
      return CityDTO::fromApiResponse($city)->toArray();
    });
  }
}
