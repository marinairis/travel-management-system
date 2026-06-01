<?php

declare(strict_types=1);

namespace App\Http\Requests\ActivityLog;

use App\Http\Traits\FailedValidationJson;
use Illuminate\Foundation\Http\FormRequest;

class ActivityLogFilterRequest extends FormRequest
{
    use FailedValidationJson;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer|min:1',
            'action' => 'nullable|string|max:100',
            'model_type' => 'nullable|string|max:100',
            'model_id' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
