<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ManagerOrAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || $user->isRequester()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para executar esta ação'
            ], 403);
        }

        return $next($request);
    }
}
