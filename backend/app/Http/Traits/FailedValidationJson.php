<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait FailedValidationJson
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => __('messages.validation.error'),
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
