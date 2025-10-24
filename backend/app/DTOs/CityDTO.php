<?php

namespace App\DTOs;

class CityDTO
{
  public function __construct(
    public readonly int $id,
    public readonly string $nome,
    public readonly string $estado,
    public readonly string $uf,
    public readonly string $label,
    public readonly string $value
  ) {}

  public static function fromApiResponse(array $data): self
  {
    $nome = $data['nome'] ?? '';

    // Safely extract estado and uf with proper null checks
    $estado = '';
    $uf = '';

    if (isset($data['microrregiao']['mesorregiao']['UF'])) {
      $estado = $data['microrregiao']['mesorregiao']['UF']['nome'] ?? '';
      $uf = $data['microrregiao']['mesorregiao']['UF']['sigla'] ?? '';
    } elseif (isset($data['estado'])) {
      $estado = $data['estado']['nome'] ?? '';
      $uf = $data['estado']['sigla'] ?? '';
    }

    return new self(
      id: $data['id'] ?? 0,
      nome: $nome,
      estado: $estado,
      uf: $uf,
      label: "{$nome} - {$estado} - {$uf}",
      value: "{$nome} - {$estado} - {$uf}"
    );
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'nome' => $this->nome,
      'estado' => $this->estado,
      'uf' => $this->uf,
      'label' => $this->label,
      'value' => $this->value,
    ];
  }
}
