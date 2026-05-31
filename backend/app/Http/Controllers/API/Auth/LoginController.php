<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.invalid_credentials'),
            ], 401);
        }

        return response()->json([
            'success'    => true,
            'message'    => __('messages.auth.login_success'),
            'data'       => ['user' => auth()->user(), 'token' => $token, 'token_type' => 'bearer'],
        ]);
    }
}
