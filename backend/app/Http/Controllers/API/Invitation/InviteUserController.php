<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Invitation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\InviteUserRequest;
use App\Models\Invitation;
use App\Notifications\UserInvited;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteUserController extends Controller
{
    public function __invoke(InviteUserRequest $request): JsonResponse
    {
        $token = Str::random(64);

        Invitation::create([
            'email' => $request->email,
            'role' => $request->role,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        try {
            Notification::route('mail', $request->email)
                ->notify(new UserInvited($token, $request->role));
        } catch (\Exception $e) {
            Log::error('Erro ao enviar convite: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('messages.invitation.email_failed'),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.invitation.sent', ['email' => $request->email]),
        ], 201);
    }
}
