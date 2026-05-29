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

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Registrar novo usuário",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Maria Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="maria@example.com"),
     *             @OA\Property(property="password", type="string", minLength=6, example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuário registrado com token JWT"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Autenticar usuário e obter token JWT",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token JWT retornado com dados do usuário"),
     *     @OA\Response(response=401, description="Credenciais inválidas"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/me",
     *     tags={"Auth"},
     *     summary="Retornar dados do usuário autenticado",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Dados do usuário autenticado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function me(Request $request)
    {
        return $this->successResponse('general.success', $request->user());
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Encerrar sessão e invalidar token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logout realizado com sucesso"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->successResponse('auth.logout_success');
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     tags={"Auth"},
     *     summary="Renovar token JWT",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Novo token JWT"),
     *     @OA\Response(response=401, description="Token inválido ou expirado")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     tags={"Auth"},
     *     summary="Solicitar link de redefinição de senha",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="maria@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Link enviado por e-mail"),
     *     @OA\Response(response=422, description="E-mail não encontrado")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     tags={"Auth"},
     *     summary="Redefinir senha com token recebido por e-mail",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"token","email","password","password_confirmation"},
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", minLength=6),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Senha redefinida com sucesso"),
     *     @OA\Response(response=400, description="Token inválido ou expirado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
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
