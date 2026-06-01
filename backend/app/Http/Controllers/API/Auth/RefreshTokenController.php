<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => __('messages.auth.token_refresh_success'),
                'data' => ['token' => $token, 'token_type' => 'bearer'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.token_refresh_error'),
            ], 401);
        }
    }
}
