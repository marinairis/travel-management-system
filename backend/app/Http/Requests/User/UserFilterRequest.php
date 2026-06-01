<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class UserFilterRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_type' => 'nullable|string|in:admin,manager,requester,basic',
            'email' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
