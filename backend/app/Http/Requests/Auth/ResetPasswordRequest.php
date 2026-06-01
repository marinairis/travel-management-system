<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ];
    }
}
