<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelRequest;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class TravelRequestFilterRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'      => 'nullable|string|in:requested,approved,cancelled,expired',
            'destination' => 'nullable|string|max:255',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ];
    }
}
