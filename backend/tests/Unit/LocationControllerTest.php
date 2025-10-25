<?php

namespace Tests\Unit;

use App\Http\Controllers\API\LocationController;
use App\Repositories\CityRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
  use RefreshDatabase;

  private LocationController $controller;
  private CityRepository $mockCityRepository;

  protected function setUp(): void
  {
    parent::setUp();
    $this->mockCityRepository = $this->createMock(CityRepository::class);
    $this->controller = new LocationController($this->mockCityRepository);
  }

  public function test_get_cities_returns_successful_response()
  {
    $mockCities = collect([
      [
        'id' => 1,
        'name' => 'São Paulo',
        'state' => 'São Paulo',
        'uf' => 'SP',
        'label' => 'São Paulo - São Paulo - SP',
        'value' => 'São Paulo - São Paulo - SP',
      ],
      [
        'id' => 2,
        'name' => 'Rio de Janeiro',
        'state' => 'Rio de Janeiro',
        'uf' => 'RJ',
        'label' => 'Rio de Janeiro - Rio de Janeiro - RJ',
        'value' => 'Rio de Janeiro - Rio de Janeiro - RJ',
      ],
    ]);

    $this->mockCityRepository
      ->expects($this->once())
      ->method('search')
      ->with('São Paulo')
      ->willReturn($mockCities);

    $request = Request::create('/cities', 'GET', ['q' => 'São Paulo']);

    $response = $this->controller->getCities($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertArrayHasKey('meta', $responseData);
    $this->assertCount(2, $responseData['data']);
    $this->assertEquals(2, $responseData['meta']['total']);
    $this->assertTrue($responseData['meta']['has_query']);
  }

  public function test_get_cities_returns_all_cities_when_no_query()
  {
    $mockCities = collect([
      [
        'id' => 1,
        'name' => 'São Paulo',
        'state' => 'São Paulo',
        'uf' => 'SP',
        'label' => 'São Paulo - São Paulo - SP',
        'value' => 'São Paulo - São Paulo - SP',
      ],
    ]);

    $this->mockCityRepository
      ->expects($this->once())
      ->method('search')
      ->with(null)
      ->willReturn($mockCities);

    $request = Request::create('/cities', 'GET');

    $response = $this->controller->getCities($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertArrayHasKey('meta', $responseData);
    $this->assertCount(1, $responseData['data']);
    $this->assertEquals(1, $responseData['meta']['total']);
    $this->assertFalse($responseData['meta']['has_query']);
  }

  public function test_get_cities_handles_exception()
  {
    $this->mockCityRepository
      ->expects($this->once())
      ->method('search')
      ->with('test')
      ->willThrowException(new \Exception('Service unavailable'));

    $request = Request::create('/cities', 'GET', ['q' => 'test']);

    $response = $this->controller->getCities($request);

    $this->assertEquals(500, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Erro ao buscar municípios', $responseData['message']);
    $this->assertArrayHasKey('error', $responseData);
  }

  public function test_get_cities_returns_empty_array_on_exception_in_production()
  {
    config(['app.debug' => false]);

    $this->mockCityRepository
      ->expects($this->once())
      ->method('search')
      ->with('test')
      ->willThrowException(new \Exception('Service unavailable'));

    $request = Request::create('/cities', 'GET', ['q' => 'test']);

    $response = $this->controller->getCities($request);

    $this->assertEquals(500, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Erro ao buscar municípios', $responseData['message']);
    $this->assertNull($responseData['error']);
  }

  public function test_get_destinations_returns_successful_response()
  {
    $mockDestinations = collect([
      [
        'value' => 'São Paulo - São Paulo - SP',
        'label' => 'São Paulo - São Paulo - SP',
        'id' => 1,
        'nome' => 'São Paulo',
        'estado' => 'São Paulo',
        'uf' => 'SP',
      ],
      [
        'value' => 'Rio de Janeiro - Rio de Janeiro - RJ',
        'label' => 'Rio de Janeiro - Rio de Janeiro - RJ',
        'id' => 2,
        'nome' => 'Rio de Janeiro',
        'estado' => 'Rio de Janeiro',
        'uf' => 'RJ',
      ],
    ]);

    $this->mockCityRepository
      ->expects($this->once())
      ->method('getDestinations')
      ->willReturn($mockDestinations);

    $response = $this->controller->getDestinations();

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertArrayHasKey('data', $responseData);
    $this->assertArrayHasKey('meta', $responseData);
    $this->assertCount(2, $responseData['data']);
    $this->assertEquals(2, $responseData['meta']['total']);
    $this->assertTrue($responseData['meta']['cached']);
  }

  public function test_get_destinations_handles_exception()
  {
    $this->mockCityRepository
      ->expects($this->once())
      ->method('getDestinations')
      ->willThrowException(new \Exception('Service unavailable'));

    $response = $this->controller->getDestinations();

    $this->assertEquals(500, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Erro ao buscar destinos', $responseData['message']);
    $this->assertArrayHasKey('error', $responseData);
  }

  public function test_get_destinations_returns_empty_array_on_exception_in_production()
  {
    config(['app.debug' => false]);

    $this->mockCityRepository
      ->expects($this->once())
      ->method('getDestinations')
      ->willThrowException(new \Exception('Service unavailable'));

    $response = $this->controller->getDestinations();

    $this->assertEquals(500, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertFalse($responseData['success']);
    $this->assertEquals('Erro ao buscar destinos', $responseData['message']);
    $this->assertNull($responseData['error']);
  }

  public function test_get_cities_returns_correct_meta_information()
  {
    $mockCities = collect([
      ['id' => 1, 'name' => 'Test City'],
      ['id' => 2, 'name' => 'Another City'],
    ]);

    $this->mockCityRepository
      ->expects($this->once())
      ->method('search')
      ->with('test')
      ->willReturn($mockCities);

    $request = Request::create('/cities', 'GET', ['q' => 'test']);

    $response = $this->controller->getCities($request);

    $responseData = $response->getData(true);
    $meta = $responseData['meta'];

    $this->assertEquals(2, $meta['total']);
    $this->assertTrue($meta['has_query']);
  }

  public function test_get_destinations_returns_correct_meta_information()
  {
    $mockDestinations = collect([
      ['id' => 1, 'nome' => 'Test City'],
    ]);

    $this->mockCityRepository
      ->expects($this->once())
      ->method('getDestinations')
      ->willReturn($mockDestinations);

    $response = $this->controller->getDestinations();

    $responseData = $response->getData(true);
    $meta = $responseData['meta'];

    $this->assertEquals(1, $meta['total']);
    $this->assertTrue($meta['cached']);
  }

  public function test_get_cities_handles_empty_query()
  {
    $mockCities = collect([]);

    $this->mockCityRepository
      ->expects($this->once())
      ->method('search')
      ->with('')
      ->willReturn($mockCities);

    $request = Request::create('/cities', 'GET', ['q' => '']);

    $response = $this->controller->getCities($request);

    $this->assertEquals(200, $response->getStatusCode());

    $responseData = $response->getData(true);
    $this->assertTrue($responseData['success']);
    $this->assertCount(0, $responseData['data']);
    $this->assertEquals(0, $responseData['meta']['total']);
    $this->assertTrue($responseData['meta']['has_query']);
  }
}
