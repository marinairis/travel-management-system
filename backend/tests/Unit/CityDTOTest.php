<?php

namespace Tests\Unit;

use App\DTOs\CityDTO;
use Tests\TestCase;

class CityDTOTest extends TestCase
{
  public function test_city_dto_can_be_created()
  {
    $dto = new CityDTO(
      id: 1,
      name: 'São Paulo',
      state: 'São Paulo',
      uf: 'SP',
      label: 'São Paulo - São Paulo - SP',
      value: 'São Paulo - São Paulo - SP'
    );

    $this->assertEquals(1, $dto->id);
    $this->assertEquals('São Paulo', $dto->name);
    $this->assertEquals('São Paulo', $dto->state);
    $this->assertEquals('SP', $dto->uf);
    $this->assertEquals('São Paulo - São Paulo - SP', $dto->label);
    $this->assertEquals('São Paulo - São Paulo - SP', $dto->value);
  }

  public function test_from_api_response_creates_dto_with_microrregiao_structure()
  {
    $apiData = [
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
    ];

    $dto = CityDTO::fromApiResponse($apiData);

    $this->assertEquals(1, $dto->id);
    $this->assertEquals('São Paulo', $dto->name);
    $this->assertEquals('São Paulo', $dto->state);
    $this->assertEquals('SP', $dto->uf);
    $this->assertEquals('São Paulo - São Paulo - SP', $dto->label);
    $this->assertEquals('São Paulo - São Paulo - SP', $dto->value);
  }

  public function test_from_api_response_creates_dto_with_estado_structure()
  {
    $apiData = [
      'id' => 2,
      'nome' => 'Rio de Janeiro',
      'estado' => [
        'nome' => 'Rio de Janeiro',
        'sigla' => 'RJ'
      ]
    ];

    $dto = CityDTO::fromApiResponse($apiData);

    $this->assertEquals(2, $dto->id);
    $this->assertEquals('Rio de Janeiro', $dto->name);
    $this->assertEquals('Rio de Janeiro', $dto->state);
    $this->assertEquals('RJ', $dto->uf);
    $this->assertEquals('Rio de Janeiro - Rio de Janeiro - RJ', $dto->label);
    $this->assertEquals('Rio de Janeiro - Rio de Janeiro - RJ', $dto->value);
  }

  public function test_from_api_response_handles_missing_data()
  {
    $apiData = [
      'id' => 3,
      'nome' => 'Test City'
    ];

    $dto = CityDTO::fromApiResponse($apiData);

    $this->assertEquals(3, $dto->id);
    $this->assertEquals('Test City', $dto->name);
    $this->assertEquals('', $dto->state);
    $this->assertEquals('', $dto->uf);
    $this->assertEquals('Test City -  - ', $dto->label);
    $this->assertEquals('Test City -  - ', $dto->value);
  }

  public function test_from_api_response_handles_empty_arrays()
  {
    $apiData = [
      'id' => 4,
      'nome' => 'Test City',
      'microrregiao' => [
        'mesorregiao' => [
          'UF' => []
        ]
      ]
    ];

    $dto = CityDTO::fromApiResponse($apiData);

    $this->assertEquals(4, $dto->id);
    $this->assertEquals('Test City', $dto->name);
    $this->assertEquals('', $dto->state);
    $this->assertEquals('', $dto->uf);
    $this->assertEquals('Test City -  - ', $dto->label);
    $this->assertEquals('Test City -  - ', $dto->value);
  }

  public function test_from_api_response_handles_missing_id()
  {
    $apiData = [
      'nome' => 'Test City',
      'microrregiao' => [
        'mesorregiao' => [
          'UF' => [
            'nome' => 'Test State',
            'sigla' => 'TS'
          ]
        ]
      ]
    ];

    $dto = CityDTO::fromApiResponse($apiData);

    $this->assertEquals(0, $dto->id);
    $this->assertEquals('Test City', $dto->name);
    $this->assertEquals('Test State', $dto->state);
    $this->assertEquals('TS', $dto->uf);
  }

  public function test_to_array_returns_correct_structure()
  {
    $dto = new CityDTO(
      id: 1,
      name: 'São Paulo',
      state: 'São Paulo',
      uf: 'SP',
      label: 'São Paulo - São Paulo - SP',
      value: 'São Paulo - São Paulo - SP'
    );

    $array = $dto->toArray();

    $expected = [
      'id' => 1,
      'name' => 'São Paulo',
      'state' => 'São Paulo',
      'uf' => 'SP',
      'label' => 'São Paulo - São Paulo - SP',
      'value' => 'São Paulo - São Paulo - SP',
    ];

    $this->assertEquals($expected, $array);
  }

  public function test_to_array_returns_all_properties()
  {
    $dto = new CityDTO(
      id: 1,
      name: 'São Paulo',
      state: 'São Paulo',
      uf: 'SP',
      label: 'São Paulo - São Paulo - SP',
      value: 'São Paulo - São Paulo - SP'
    );

    $array = $dto->toArray();

    $this->assertArrayHasKey('id', $array);
    $this->assertArrayHasKey('name', $array);
    $this->assertArrayHasKey('state', $array);
    $this->assertArrayHasKey('uf', $array);
    $this->assertArrayHasKey('label', $array);
    $this->assertArrayHasKey('value', $array);
  }

  public function test_properties_are_readonly()
  {
    $dto = new CityDTO(
      id: 1,
      name: 'São Paulo',
      state: 'São Paulo',
      uf: 'SP',
      label: 'São Paulo - São Paulo - SP',
      value: 'São Paulo - São Paulo - SP'
    );

    $this->assertEquals(1, $dto->id);
    $this->assertEquals('São Paulo', $dto->name);
    $this->assertEquals('São Paulo', $dto->state);
    $this->assertEquals('SP', $dto->uf);
    $this->assertEquals('São Paulo - São Paulo - SP', $dto->label);
    $this->assertEquals('São Paulo - São Paulo - SP', $dto->value);
  }

  public function test_handles_special_characters_in_names()
  {
    $apiData = [
      'id' => 1,
      'nome' => 'São José dos Campos',
      'microrregiao' => [
        'mesorregiao' => [
          'UF' => [
            'nome' => 'São Paulo',
            'sigla' => 'SP'
          ]
        ]
      ]
    ];

    $dto = CityDTO::fromApiResponse($apiData);

    $this->assertEquals('São José dos Campos', $dto->name);
    $this->assertEquals('São Paulo', $dto->state);
    $this->assertEquals('SP', $dto->uf);
    $this->assertEquals('São José dos Campos - São Paulo - SP', $dto->label);
    $this->assertEquals('São José dos Campos - São Paulo - SP', $dto->value);
  }

  public function test_handles_long_city_names()
  {
    $longCityName = 'Very Long City Name That Should Be Handled Properly Without Any Issues';

    $apiData = [
      'id' => 1,
      'nome' => $longCityName,
      'microrregiao' => [
        'mesorregiao' => [
          'UF' => [
            'nome' => 'Test State',
            'sigla' => 'TS'
          ]
        ]
      ]
    ];

    $dto = CityDTO::fromApiResponse($apiData);

    $this->assertEquals($longCityName, $dto->name);
    $this->assertEquals('Test State', $dto->state);
    $this->assertEquals('TS', $dto->uf);
    $this->assertEquals("{$longCityName} - Test State - TS", $dto->label);
    $this->assertEquals("{$longCityName} - Test State - TS", $dto->value);
  }
}
