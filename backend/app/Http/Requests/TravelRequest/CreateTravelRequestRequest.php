<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelRequest;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class CreateTravelRequestRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after:departure_date',
            'notes' => 'nullable|string',
            'travel_type' => 'nullable|string|in:bus,plane,car,hotel',
        ];
    }

    public function messages(): array
    {
        return [
            'requester_name.required' => __('messages.validation.requester_name.required'),
            'requester_name.max' => __('messages.validation.requester_name.max'),
            'destination.required' => __('messages.validation.destination.required'),
            'departure_date.required' => __('messages.validation.departure_date.required'),
            'departure_date.date' => __('messages.validation.departure_date.date'),
            'departure_date.after_or_equal' => __('messages.validation.departure_date.after_or_equal'),
            'return_date.required' => __('messages.validation.return_date.required'),
            'return_date.date' => __('messages.validation.return_date.date'),
            'return_date.after' => __('messages.validation.return_date.after'),
        ];
    }
}
