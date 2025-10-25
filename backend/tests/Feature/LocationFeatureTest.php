<?php

namespace Tests\Feature;

use App\Services\IbgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocationFeatureTest extends TestCase
{
  use RefreshDatabase;

  public function test_can_get_cities_without_query()
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

    $response = $this->getJson('/api/cities');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'data' => [
          '*' => [
            'id',
            'name',
            'state',
            'uf',
            'label',
            'value',
          ],
        ],
        'meta' => [
          'total',
          'has_query',
        ],
      ])
      ->assertJson([
        'success' => true,
        'meta' => [
          'total' => 2,
          'has_query' => false,
        ],
      ]);
  }

  public function test_can_search_cities_with_query()
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
      ],
      [
        'id' => 3,
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

    $response = $this->getJson('/api/cities?q=São');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'data' => [
          '*' => [
            'id',
            'name',
            'state',
            'uf',
            'label',
            'value',
          ],
        ],
        'meta' => [
          'total',
          'has_query',
        ],
      ])
      ->assertJson([
        'success' => true,
        'meta' => [
          'has_query' => true,
        ],
      ]);

    $responseData = $response->json('data');

    foreach ($responseData as $city) {
      $this->assertStringContainsString('São', $city['name']);
    }
  }

  public function test_can_get_destinations()
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

    $response = $this->getJson('/api/destinations');

    $response->assertStatus(200)
      ->assertJsonStructure([
        'success',
        'data' => [
          '*' => [
            'value',
            'label',
            'id',
            'nome',
            'estado',
            'uf',
          ],
        ],
        'meta' => [
          'total',
          'cached',
        ],
      ])
      ->assertJson([
        'success' => true,
        'meta' => [
          'total' => 2,
          'cached' => true,
        ],
      ]);
  }

  public function test_handles_ibge_service_error()
  {
    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response([], 500)
    ]);

    $response = $this->getJson('/api/cities');

    $response->assertStatus(500)
      ->assertJson([
        'success' => false,
        'message' => 'Erro ao buscar municípios',
      ]);
  }

  public function test_handles_ibge_service_timeout()
  {
    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response([], 408)
    ]);

    $response = $this->getJson('/api/cities');

    $response->assertStatus(500)
      ->assertJson([
        'success' => false,
        'message' => 'Erro ao buscar municípios',
      ]);
  }

  public function test_returns_empty_array_when_no_cities_found()
  {
    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response([], 200)
    ]);

    $response = $this->getJson('/api/cities');

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
        'data' => [],
        'meta' => [
          'total' => 0,
          'has_query' => false,
        ],
      ]);
  }

  public function test_search_returns_empty_array_when_no_matches()
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

    $response = $this->getJson('/api/cities?q=NonexistentCity');

    $response->assertStatus(200)
      ->assertJson([
        'success' => true,
        'data' => [],
        'meta' => [
          'total' => 0,
          'has_query' => true,
        ],
      ]);
  }

  public function test_cities_response_format()
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

    $response = $this->getJson('/api/cities');

    $response->assertStatus(200);
    $cityData = $response->json('data.0');

    $this->assertEquals(1, $cityData['id']);
    $this->assertEquals('São Paulo', $cityData['name']);
    $this->assertEquals('São Paulo', $cityData['state']);
    $this->assertEquals('SP', $cityData['uf']);
    $this->assertEquals('São Paulo - São Paulo - SP', $cityData['label']);
    $this->assertEquals('São Paulo - São Paulo - SP', $cityData['value']);
  }

  public function test_destinations_response_format()
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

    $response = $this->getJson('/api/destinations');

    $response->assertStatus(200);
    $destinationData = $response->json('data.0');

    $this->assertEquals(1, $destinationData['id']);
    $this->assertEquals('São Paulo', $destinationData['nome']);
    $this->assertEquals('São Paulo', $destinationData['estado']);
    $this->assertEquals('SP', $destinationData['uf']);
    $this->assertEquals('São Paulo - São Paulo - SP', $destinationData['value']);
    $this->assertEquals('São Paulo - São Paulo - SP', $destinationData['label']);
  }

  public function test_cities_endpoint_is_public()
  {
    $response = $this->getJson('/api/cities');

    $response->assertStatus(200);
  }

  public function test_destinations_endpoint_is_public()
  {
    $response = $this->getJson('/api/destinations');

    $response->assertStatus(200);
  }

  public function test_handles_malformed_ibge_response()
  {
    $malformedData = [
      [
        'id' => 1,
        'nome' => 'Test City',
      ]
    ];

    Http::fake([
      'servicodados.ibge.gov.br/api/v1/localidades/municipios' => Http::response($malformedData, 200)
    ]);

    $response = $this->getJson('/api/cities');

    $response->assertStatus(200);
    $cityData = $response->json('data.0');

    $this->assertEquals(1, $cityData['id']);
    $this->assertEquals('Test City', $cityData['name']);
    $this->assertEquals('', $cityData['state']);
    $this->assertEquals('', $cityData['uf']);
    $this->assertEquals('Test City -  - ', $cityData['label']);
    $this->assertEquals('Test City -  - ', $cityData['value']);
  }
}
