<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TravelRequestStatusRequest extends FormRequest
{
  public function authorize(): bool
  {
    return Auth::user()->is_admin;
  }

  public function rules(): array
  {
    return [
      'status' => 'required|in:approved,cancelled',
    ];
  }

  public function messages(): array
  {
    return [
      'status.required' => 'O status é obrigatório',
      'status.in' => 'O status deve ser "approved" ou "cancelled"',
    ];
  }

  public function attributes(): array
  {
    return [
      'status' => 'status',
    ];
  }
}
