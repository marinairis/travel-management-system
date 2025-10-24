<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TravelRequestFormRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $rules = [
      'requester_name' => 'required|string|max:255',
      'destination' => 'required|string|max:255',
      'departure_date' => 'required|date|after_or_equal:today',
      'return_date' => 'required|date|after:departure_date',
      'notes' => 'nullable|string',
    ];

    if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
      $rules = [
        'requester_name' => 'sometimes|string|max:255',
        'destination' => 'sometimes|string|max:255',
        'departure_date' => 'sometimes|date|after_or_equal:today',
        'return_date' => 'sometimes|date|after:departure_date',
        'notes' => 'nullable|string',
      ];
    }

    return $rules;
  }

  public function messages(): array
  {
    return [
      'requester_name.required' => 'O nome do solicitante é obrigatório',
      'requester_name.string' => 'O nome do solicitante deve ser um texto',
      'requester_name.max' => 'O nome do solicitante não pode ter mais de 255 caracteres',
      'destination.required' => 'O destino é obrigatório',
      'destination.string' => 'O destino deve ser um texto',
      'destination.max' => 'O destino não pode ter mais de 255 caracteres',
      'departure_date.required' => 'A data de partida é obrigatória',
      'departure_date.date' => 'A data de partida deve ser uma data válida',
      'departure_date.after_or_equal' => 'A data de partida deve ser hoje ou no futuro',
      'return_date.required' => 'A data de retorno é obrigatória',
      'return_date.date' => 'A data de retorno deve ser uma data válida',
      'return_date.after' => 'A data de retorno deve ser após a data de partida',
      'notes.string' => 'As observações devem ser um texto',
    ];
  }
}
