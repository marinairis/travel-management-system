<?php

namespace App\DTOs;

class CityDTO
{
  public function __construct(
    public readonly int $id,
    public readonly string $name,
    public readonly string $state,
    public readonly string $uf,
    public readonly string $label,
    public readonly string $value
  ) {}

  public static function fromApiResponse(array $data): self
  {
    $name = $data['nome'] ?? '';

    $state = '';
    $uf = '';

    if (isset($data['microrregiao']['mesorregiao']['UF'])) {
      $state = $data['microrregiao']['mesorregiao']['UF']['nome'] ?? '';
      $uf = $data['microrregiao']['mesorregiao']['UF']['sigla'] ?? '';
    } elseif (isset($data['estado'])) {
      $state = $data['estado']['nome'] ?? '';
      $uf = $data['estado']['sigla'] ?? '';
    }

    return new self(
      id: $data['id'] ?? 0,
      name: $name,
      state: $state,
      uf: $uf,
      label: "{$name} - {$state} - {$uf}",
      value: "{$name} - {$state} - {$uf}"
    );
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'state' => $this->state,
      'uf' => $this->uf,
      'label' => $this->label,
      'value' => $this->value,
    ];
  }
}
