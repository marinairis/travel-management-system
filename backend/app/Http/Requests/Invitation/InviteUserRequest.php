<?php

declare(strict_types=1);

namespace App\Http\Requests\Invitation;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class InviteUserRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'unique:invitations,email',
            ],
            'role' => 'required|in:requester,manager,admin',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => __('messages.invitation.email_already_exists'),
        ];
    }
}
