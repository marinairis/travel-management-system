<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthFormRequest;
use App\Models\User;
use App\Traits\HasActivityLogging;
use App\Traits\HasTranslations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use HasActivityLogging, HasTranslations;

    public function register(AuthFormRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false,
        ]);

        $this->logActivityCreate($user, $request, $this->translate('auth.register_success'));

        $token = JWTAuth::fromUser($user);

        return $this->successResponse('auth.register_success', [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer'
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return $this->errorResponse('auth.invalid_credentials', null, 401);
        }

        $user = Auth::user();

        return $this->successResponse('auth.login_success', [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer'
        ]);
    }

    public function me(Request $request)
    {
        return $this->successResponse('general.success', $request->user());
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->successResponse('auth.logout_success');
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return $this->successResponse('auth.token_refresh_success', [
                'token' => $token,
                'token_type' => 'bearer'
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('auth.token_refresh_error', null, 401);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse('auth.forgot_password_success');
            }

            return $this->errorResponse('auth.forgot_password_error', null, 500);
        } catch (\Exception $e) {
            return $this->errorResponse('general.server_error', null, 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse('auth.reset_password_success');
        }

        $errorMessages = [
            Password::INVALID_TOKEN => 'auth.reset_password_invalid_token',
            Password::INVALID_USER => 'auth.reset_password_invalid_user',
            Password::RESET_THROTTLED => 'auth.reset_password_throttled',
        ];

        $messageKey = $errorMessages[$status] ?? 'auth.reset_password_error';

        return $this->errorResponse($messageKey, null, 400);
    }
}
