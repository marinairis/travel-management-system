<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Invitation;

use App\Exceptions\Invitation\InvitationException;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Notifications\UserInvited;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResendInvitationController extends Controller
{
    public function __invoke(int $id): JsonResponse
    {
        $invitation = Invitation::find($id);

        if (! $invitation) {
            throw new InvitationException(InvitationException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if ($invitation->accepted_at) {
            throw new InvitationException(InvitationException::ALREADY_USED, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = Str::random(64);
        $invitation->token      = $token;
        $invitation->expires_at = now()->addDays(7);
        $invitation->save();

        try {
            Notification::route('mail', $invitation->email)
                ->notify(new UserInvited($token, $invitation->role));
        } catch (\Exception $e) {
            Log::error('Erro ao reenviar convite: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('messages.invitation.email_failed'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.invitation.resent', ['email' => $invitation->email]),
        ]);
    }
}
