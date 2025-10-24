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

  public function searchCities(string $query = null)
  {
    $municipios = $this->getCity();

    if (!$query) {
      return $municipios;
    }

    return collect($municipios)->filter(function ($municipio) use ($query) {
      $searchable = strtolower(
        $municipio['nome'] . ' ' .
          $municipio['microrregiao']['mesorregiao']['UF']['sigla'] . ' ' .
          $municipio['microrregiao']['mesorregiao']['UF']['nome']
      );

      return str_contains($searchable, strtolower($query));
    });
  }
}
