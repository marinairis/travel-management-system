<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
  public function handle(Request $request, Closure $next): Response
  {
    $user = Auth::user();

    if (!$user || !$user->is_admin) {
      return response()->json([
        'success' => false,
        'message' => 'Apenas administradores podem acessar este recurso'
      ], 403);
    }

    return $next($request);
  }
}
