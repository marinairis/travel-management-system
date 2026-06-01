<?php

declare(strict_types=1);

namespace App\Http\Requests\Invitation;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class AcceptInvitationRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => __('messages.validation.password.regex'),
        ];
    }
}
