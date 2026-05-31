<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelRequest;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTravelRequestRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_name' => 'sometimes|string|max:255',
            'destination'    => 'sometimes|string|max:255',
            'departure_date' => 'sometimes|date|after_or_equal:today',
            'return_date'    => 'sometimes|date|after:departure_date',
            'notes'          => 'nullable|string',
            'travel_type'    => 'nullable|string|in:bus,plane,car,hotel',
        ];
    }

    public function messages(): array
    {
        return [
            'departure_date.after_or_equal' => __('messages.validation.departure_date.after_or_equal'),
            'return_date.after'             => __('messages.validation.return_date.after'),
        ];
    }
}
