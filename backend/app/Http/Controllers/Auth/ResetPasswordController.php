<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
  public function showResetForm(Request $request, $token = null)
  {
    return response()->json([
      'token' => $token,
      'email' => $request->email
    ]);
  }

  public function reset(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:6|confirmed',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Erro de validação',
        'errors' => $validator->errors()
      ], 422);
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
      return response()->json([
        'success' => true,
        'message' => 'Senha alterada com sucesso'
      ]);
    }

    return response()->json([
      'success' => false,
      'message' => 'Erro ao alterar senha: ' . $status
    ], 500);
  }
}
