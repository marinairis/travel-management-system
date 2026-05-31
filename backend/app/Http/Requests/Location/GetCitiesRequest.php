<?php

declare(strict_types=1);

namespace App\Http\Requests\Location;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class GetCitiesRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
