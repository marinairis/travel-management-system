<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => __('messages.auth.reset_password_success'),
            ]);
        }

        $messageKeys = [
            Password::INVALID_TOKEN => 'auth.reset_password_invalid_token',
            Password::INVALID_USER  => 'auth.reset_password_invalid_user',
            Password::RESET_THROTTLED => 'auth.reset_password_throttled',
        ];

        $key = $messageKeys[$status] ?? 'auth.reset_password_error';

        return response()->json([
            'success' => false,
            'message' => __("messages.{$key}"),
        ], 400);
    }
}
