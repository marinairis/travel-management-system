<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_type' => 'nullable|in:admin,manager,requester,basic',
            'status' => 'nullable|in:active,inactive',
            'email' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'user_type.in' => 'O tipo de usuário deve ser admin, manager ou requester',
            'status.in' => 'O status deve ser active ou inactive',
            'email.string' => 'O email deve ser um texto',
            'email.max' => 'O email não pode ter mais de 255 caracteres',
        ];
    }
}
