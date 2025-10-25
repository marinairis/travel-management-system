<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $locale = $request->header('Accept-Language', config('locale.default_locale', 'pt-BR'));
    $availableLocales = array_keys(config('locale.available_locales', ['pt-BR' => 'Português (Brasil)']));

    // Validate locale
    if (!in_array($locale, $availableLocales)) {
      $locale = config('locale.fallback_locale', 'pt-BR');
    }

    app()->setLocale($locale);

    return $next($request);
  }
}
