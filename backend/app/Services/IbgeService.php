<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IbgeService
{
  private const BASE_URL = 'https://servicodados.ibge.gov.br/api/v1';
  private const CACHE_TTL = 86400; // 24 horas

  public function getCity()
  {
    return Cache::remember('ibge_municipios', self::CACHE_TTL, function () {
      $response = Http::timeout(30)->get(self::BASE_URL . '/localidades/municipios');

      if (!$response->successful()) {
        throw new \Exception('Falha ao buscar dados do IBGE: ' . $response->status());
      }

      return $response->json();
    });
  }

  public function searchCities(string|null $query = null)
  {
    $cities = $this->getCity();

    if (!$query) {
      return $cities;
    }

    return collect($cities)->filter(function ($city) use ($query) {
      $searchable = strtolower(
        $city['nome'] . ' ' .
          $city['microrregiao']['mesorregiao']['UF']['sigla'] . ' ' .
          $city['microrregiao']['mesorregiao']['UF']['nome']
      );

      return str_contains($searchable, strtolower($query));
    });
  }
}
