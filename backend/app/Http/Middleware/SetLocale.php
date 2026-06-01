<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['pt-BR', 'en', 'es'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', 'pt-BR');
        App::setLocale(in_array($locale, self::SUPPORTED, strict: true) ? $locale : 'pt-BR');

        return $next($request);
    }
}
