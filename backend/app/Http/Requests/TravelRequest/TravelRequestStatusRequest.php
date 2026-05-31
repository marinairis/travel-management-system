<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelRequest;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class TravelRequestStatusRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:approved,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => __('messages.validation.status.required'),
            'status.in'       => __('messages.validation.status.in'),
        ];
    }
}
