<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (\Exception) {
            // Token already invalid or missing — logout still succeeds
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.logout_success'),
        ]);
    }
}
