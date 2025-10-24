<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserFormRequest extends FormRequest
{
  public function authorize(): bool
  {
    return Auth::user()->is_admin;
  }

  public function rules(): array
  {
    return [
      'name' => 'sometimes|string|max:255',
      'is_admin' => 'sometimes|boolean',
    ];
  }

  public function messages(): array
  {
    return [
      'name.string' => 'O nome deve ser um texto',
      'name.max' => 'O nome não pode ter mais de 255 caracteres',
      'is_admin.boolean' => 'O campo is_admin deve ser verdadeiro ou falso',
    ];
  }
}
