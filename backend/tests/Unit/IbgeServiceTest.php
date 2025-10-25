<?php

namespace Tests\Unit;

use App\Services\IbgeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class IbgeServiceTest extends TestCase
{
  private IbgeService $ibgeService;

  protected function setUp(): void
  {
    parent::setUp();
    $this->ibgeService = new IbgeService();
    Cache::flush();
  }

  public function test_get_city_returns_cached_data()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ],
      [
        'id' => 2,
        'nome' => 'Rio de Janeiro',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'Rio de Janeiro',
              'sigla' => 'RJ'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result1 = $this->ibgeService->getCity();

    $result2 = $this->ibgeService->getCity();

    $this->assertEquals($mockData, $result1);
    $this->assertEquals($mockData, $result2);

    Http::assertSentCount(1);
  }

  public function test_get_city_throws_exception_on_http_error()
  {
    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response([], 500)
    ]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Falha ao buscar dados do IBGE: 500');

    $this->ibgeService->getCity();
  }

  public function test_get_city_throws_exception_on_timeout()
  {
    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response([], 408)
    ]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Falha ao buscar dados do IBGE: 408');

    $this->ibgeService->getCity();
  }

  public function test_search_cities_returns_all_cities_when_no_query()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result = $this->ibgeService->searchCities();

    $this->assertEquals($mockData, $result);
  }

  public function test_search_cities_filters_by_query()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ],
      [
        'id' => 2,
        'nome' => 'Rio de Janeiro',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'Rio de Janeiro',
              'sigla' => 'RJ'
            ]
          ]
        ]
      ],
      [
        'id' => 3,
        'nome' => 'Belo Horizonte',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'Minas Gerais',
              'sigla' => 'MG'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result = $this->ibgeService->searchCities('São Paulo');

    $this->assertCount(1, $result);
    $this->assertEquals('São Paulo', $result->first()['nome']);
  }

  public function test_search_cities_filters_by_state_name()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ],
      [
        'id' => 2,
        'nome' => 'Rio de Janeiro',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'Rio de Janeiro',
              'sigla' => 'RJ'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result = $this->ibgeService->searchCities('SP');

    $this->assertCount(1, $result);
    $this->assertEquals('São Paulo', $result->first()['nome']);
  }

  public function test_search_cities_filters_by_state_abbreviation()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ],
      [
        'id' => 2,
        'nome' => 'Rio de Janeiro',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'Rio de Janeiro',
              'sigla' => 'RJ'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result = $this->ibgeService->searchCities('RJ');

    $this->assertCount(1, $result);
    $this->assertEquals('Rio de Janeiro', $result->first()['nome']);
  }

  public function test_search_cities_is_case_insensitive()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result = $this->ibgeService->searchCities('são paulo');

    $this->assertCount(1, $result);
    $this->assertEquals('São Paulo', $result->first()['nome']);
  }

  public function test_search_cities_returns_empty_collection_when_no_matches()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result = $this->ibgeService->searchCities('NonExistentCity');

    $this->assertCount(0, $result);
  }

  public function test_search_cities_handles_partial_matches()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ],
      [
        'id' => 2,
        'nome' => 'São José dos Campos',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result = $this->ibgeService->searchCities('São');

    $this->assertCount(2, $result);
  }

  public function test_search_cities_handles_null_query()
  {
    $mockData = [
      [
        'id' => 1,
        'nome' => 'São Paulo',
        'microrregiao' => [
          'mesorregiao' => [
            'UF' => [
              'nome' => 'São Paulo',
              'sigla' => 'SP'
            ]
          ]
        ]
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($mockData, 200)
    ]);

    $result = $this->ibgeService->searchCities(null);

    $this->assertEquals($mockData, $result);
  }
}
