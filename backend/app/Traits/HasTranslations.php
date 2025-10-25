<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasTranslations
{
  /**
   * Get the current locale from request header or default to pt-BR
   */
  protected function getLocale(Request $request): string
  {
    $locale = $request->header('Accept-Language', 'pt-BR');

    // Validate locale
    if (!in_array($locale, ['pt-BR', 'en-US'])) {
      $locale = 'pt-BR';
    }

    return $locale;
  }

  /**
   * Translate a message key
   */
  protected function translate(string $key, array $replace = [], string $locale = null): string
  {
    if (!$locale) {
      $locale = app()->getLocale();
    }

    return __("messages.{$key}", $replace, $locale);
  }

  /**
   * Get success response with translated message
   */
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

  /**
   * Get error response with translated message
   */
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

  /**
   * Get validation error response with translated messages
   */
  protected function validationErrorResponse($errors, string $messageKey = 'validation.error')
  {
    $message = $this->translate($messageKey);

    return response()->json([
      'success' => false,
      'message' => $message,
      'errors' => $errors
    ], 422);
  }

  /**
   * Set locale for the current request
   */
  protected function setLocale(string $locale): void
  {
    app()->setLocale($locale);
  }
}
