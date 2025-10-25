<?php

namespace Tests\Unit;

use App\Repositories\CityRepository;
use App\Services\IbgeService;
use App\DTOs\CityDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CityRepositoryTest extends TestCase
{
  private CityRepository $cityRepository;
  private IbgeService $mockIbgeService;

  protected function setUp(): void
  {
    parent::setUp();
    $this->mockIbgeService = $this->createMock(IbgeService::class);
    $this->cityRepository = new CityRepository($this->mockIbgeService);
    Cache::flush();
  }

  public function test_get_all_returns_cached_data()
  {
    $mockApiData = [
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

    $this->mockIbgeService
      ->expects($this->once())
      ->method('getCity')
      ->willReturn($mockApiData);

    $result1 = $this->cityRepository->getAll();

    $result2 = $this->cityRepository->getAll();

    $this->assertInstanceOf(Collection::class, $result1);
    $this->assertInstanceOf(Collection::class, $result2);
    $this->assertCount(1, $result1);
    $this->assertCount(1, $result2);

    $cityData = $result1->first();
    $this->assertArrayHasKey('id', $cityData);
    $this->assertArrayHasKey('name', $cityData);
    $this->assertArrayHasKey('state', $cityData);
    $this->assertArrayHasKey('uf', $cityData);
    $this->assertArrayHasKey('label', $cityData);
    $this->assertArrayHasKey('value', $cityData);
  }

  public function test_get_destinations_returns_cached_data()
  {
    $mockApiData = [
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

    $this->mockIbgeService
      ->expects($this->once())
      ->method('getCity')
      ->willReturn($mockApiData);

    $result1 = $this->cityRepository->getDestinations();

    $result2 = $this->cityRepository->getDestinations();

    $this->assertInstanceOf(Collection::class, $result1);
    $this->assertInstanceOf(Collection::class, $result2);
    $this->assertCount(1, $result1);
    $this->assertCount(1, $result2);

    $cityData = $result1->first();
    $this->assertArrayHasKey('value', $cityData);
    $this->assertArrayHasKey('label', $cityData);
    $this->assertArrayHasKey('id', $cityData);
    $this->assertArrayHasKey('nome', $cityData);
    $this->assertArrayHasKey('estado', $cityData);
    $this->assertArrayHasKey('uf', $cityData);
  }

  public function test_search_returns_all_cities_when_no_query()
  {
    $mockApiData = [
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

    $this->mockIbgeService
      ->expects($this->once())
      ->method('getCity')
      ->willReturn($mockApiData);

    $result = $this->cityRepository->search();

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertCount(1, $result);
  }

  public function test_search_returns_filtered_cities_when_query_provided()
  {
    $mockApiData = [
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

    $filteredData = collect([$mockApiData[0]]);

    $this->mockIbgeService
      ->expects($this->once())
      ->method('searchCities')
      ->with('São Paulo')
      ->willReturn($filteredData);

    $result = $this->cityRepository->search('São Paulo');

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertCount(1, $result);
  }

  public function test_search_handles_null_query()
  {
    $mockApiData = [
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

    $this->mockIbgeService
      ->expects($this->once())
      ->method('getCity')
      ->willReturn($mockApiData);

    $result = $this->cityRepository->search(null);

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertCount(1, $result);
  }

  public function test_format_cities_creates_correct_dto_structure()
  {
    $mockApiData = [
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

    $this->mockIbgeService
      ->expects($this->once())
      ->method('getCity')
      ->willReturn($mockApiData);

    $result = $this->cityRepository->getAll();

    $cityData = $result->first();
    $this->assertEquals(1, $cityData['id']);
    $this->assertEquals('São Paulo', $cityData['name']);
    $this->assertEquals('São Paulo', $cityData['state']);
    $this->assertEquals('SP', $cityData['uf']);
    $this->assertEquals('São Paulo - São Paulo - SP', $cityData['label']);
    $this->assertEquals('São Paulo - São Paulo - SP', $cityData['value']);
  }

  public function test_format_cities_for_select_creates_correct_structure()
  {
    $mockApiData = [
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

    $this->mockIbgeService
      ->expects($this->once())
      ->method('getCity')
      ->willReturn($mockApiData);

    $result = $this->cityRepository->getDestinations();

    $cityData = $result->first();
    $this->assertEquals(1, $cityData['id']);
    $this->assertEquals('São Paulo', $cityData['nome']);
    $this->assertEquals('São Paulo', $cityData['estado']);
    $this->assertEquals('SP', $cityData['uf']);
    $this->assertEquals('São Paulo - São Paulo - SP', $cityData['value']);
    $this->assertEquals('São Paulo - São Paulo - SP', $cityData['label']);
  }

  public function test_handles_missing_uf_data()
  {
    $mockApiData = [
      [
        'id' => 1,
        'nome' => 'Test City',
        'estado' => [
          'nome' => 'Test State',
          'sigla' => 'TS'
        ]
      ]
    ];

    $this->mockIbgeService
      ->expects($this->once())
      ->method('getCity')
      ->willReturn($mockApiData);

    $result = $this->cityRepository->getAll();

    $cityData = $result->first();
    $this->assertEquals('Test City', $cityData['name']);
    $this->assertEquals('Test State', $cityData['state']);
    $this->assertEquals('TS', $cityData['uf']);
  }

  public function test_handles_empty_api_response()
  {
    $this->mockIbgeService
      ->expects($this->once())
      ->method('getCity')
      ->willReturn([]);

    $result = $this->cityRepository->getAll();

    $this->assertInstanceOf(Collection::class, $result);
    $this->assertCount(0, $result);
  }
}
