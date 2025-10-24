<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthFormRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $rules = [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:6|confirmed',
    ];

    if ($this->isMethod('POST') && $this->routeIs('login')) {
      $rules = [
        'email' => 'required|string|email',
        'password' => 'required|string',
      ];
    }

    return $rules;
  }

  public function messages(): array
  {
    return [
      'name.required' => 'O nome é obrigatório',
      'name.string' => 'O nome deve ser um texto',
      'name.max' => 'O nome não pode ter mais de 255 caracteres',
      'email.required' => 'O email é obrigatório',
      'email.string' => 'O email deve ser um texto',
      'email.email' => 'O email deve ter um formato válido',
      'email.max' => 'O email não pode ter mais de 255 caracteres',
      'email.unique' => 'Este email já está cadastrado',
      'password.required' => 'A senha é obrigatória',
      'password.string' => 'A senha deve ser um texto',
      'password.min' => 'A senha deve ter pelo menos 6 caracteres',
      'password.confirmed' => 'A confirmação da senha não confere',
    ];
  }
}
