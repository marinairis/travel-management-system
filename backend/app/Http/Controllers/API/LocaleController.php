<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
  /**
   * Get available locales
   */
  public function index()
  {
    return response()->json([
      'success' => true,
      'data' => [
        'locales' => config('locale.available_locales', []),
        'default' => config('locale.default_locale', 'pt-BR'),
        'fallback' => config('locale.fallback_locale', 'pt-BR'),
      ]
    ]);
  }

  /**
   * Get current locale
   */
  public function current(Request $request)
  {
    return response()->json([
      'success' => true,
      'data' => [
        'locale' => app()->getLocale(),
      ]
    ]);
  }
}
