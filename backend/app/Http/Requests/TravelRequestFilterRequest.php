<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TravelRequestFilterRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'status' => 'nullable|in:requested,approved,cancelled',
      'destination' => 'nullable|string|max:255',
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
      'per_page' => 'nullable|integer|min:1|max:100',
    ];
  }

  public function messages(): array
  {
    return [
      'status.in' => 'O status deve ser: requested, approved ou cancelled',
      'destination.string' => 'O destino deve ser um texto',
      'destination.max' => 'O destino não pode ter mais de 255 caracteres',
      'start_date.date' => 'A data de início deve ser uma data válida',
      'end_date.date' => 'A data de fim deve ser uma data válida',
      'end_date.after_or_equal' => 'A data de fim deve ser igual ou posterior à data de início',
      'per_page.integer' => 'O número de itens por página deve ser um número inteiro',
      'per_page.min' => 'O número de itens por página deve ser pelo menos 1',
      'per_page.max' => 'O número de itens por página não pode ser maior que 100',
    ];
  }

  public function attributes(): array
  {
    return [
      'start_date' => 'data de início',
      'end_date' => 'data de fim',
      'per_page' => 'itens por página',
    ];
  }
}
