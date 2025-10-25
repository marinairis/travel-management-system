<?php

namespace App\Traits;

trait HasTranslations
{
  protected function translate(string $key, array $replace = []): string
  {
    return __("messages.{$key}", $replace, 'pt-BR');
  }

  protected function successResponse(string $messageKey, $data = null, int $status = 200, array $replace = [])
  {
    $message = $this->translate($messageKey, $replace);

    $response = [
      'success' => true,
      'message' => $message,
    ];

    if ($data !== null) {
      $response['data'] = $data;
    }

    return response()->json($response, $status);
  }

  protected function errorResponse(string $messageKey, $errors = null, int $status = 400, array $replace = [])
  {
    $message = $this->translate($messageKey, $replace);

    $response = [
      'success' => false,
      'message' => $message,
    ];

    if ($errors !== null) {
      $response['errors'] = $errors;
    }

    return response()->json($response, $status);
  }

  protected function validationErrorResponse($errors, string $messageKey = 'validation.error')
  {
    $message = $this->translate($messageKey);

    return response()->json([
      'success' => false,
      'message' => $message,
      'errors' => $errors
    ], 422);
  }
}
