<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelRequest;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class CancelTravelRequestRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.string' => __('messages.validation.reason.string'),
            'reason.max' => __('messages.validation.reason.max'),
        ];
    }
}
