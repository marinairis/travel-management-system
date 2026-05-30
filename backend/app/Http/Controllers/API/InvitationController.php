<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvitationController extends Controller
{
    public function invite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'unique:invitations,email',
            ],
            'role' => 'required|in:requester,manager,admin',
        ], [
            'email.unique' => 'Este email já está cadastrado ou possui um convite pendente.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $token = Str::random(64);

        Invitation::create([
            'email' => $request->email,
            'role' => $request->role,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        Notification::route('mail', $request->email)
            ->notify(new UserInvited($token, $request->role));

        return response()->json([
            'success' => true,
            'message' => 'Convite enviado com sucesso para ' . $request->email,
        ], 201);
    }

    public function show($token)
    {
        $invitation = Invitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Convite não encontrado, expirado ou já utilizado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $invitation->email,
                'role' => $invitation->role,
            ]
        ]);
    }

    public function accept(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Convite não encontrado, expirado ou já utilizado.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'password.regex' => 'A senha deve conter letra maiúscula, número e símbolo.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'role' => $invitation->role,
        ]);

        $invitation->accepted_at = now();
        $invitation->save();

        $jwtToken = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Conta criada com sucesso!',
            'data' => [
                'user' => $user,
                'token' => $jwtToken,
                'token_type' => 'bearer',
            ]
        ], 201);
    }
}
