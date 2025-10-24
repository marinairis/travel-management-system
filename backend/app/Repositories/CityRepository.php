<?php

namespace App\Repositories;

use App\Services\IbgeService;
use App\DTOs\CityDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CityRepository
{
  public function __construct(private IbgeService $ibgeService) {}

  public function getAll(): Collection
  {
    return Cache::remember('destinations_all', 86400, function () {
      return $this->formatCities($this->ibgeService->getCity());
    });
  }

  public function getDestinations(): Collection
  {
    return Cache::remember('destinations_select', 86400, function () {
      $cities = $this->ibgeService->getCity();
      return $this->formatCitiesForSelect($cities);
    });
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

  private function formatCitiesForSelect($cities): Collection
  {
    return collect($cities)->map(function ($city) {
      $dto = CityDTO::fromApiResponse($city);
      return [
        'value' => $dto->value,
        'label' => $dto->label,
        'id' => $dto->id,
        'nome' => $dto->nome,
        'estado' => $dto->estado,
        'uf' => $dto->uf,
      ];
    });
  }
}
