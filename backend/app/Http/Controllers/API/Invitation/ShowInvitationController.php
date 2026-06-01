<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Invitation;

use App\Exceptions\Invitation\InvitationException;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ShowInvitationController extends Controller
{
    public function __invoke(string $token): JsonResponse
    {
        $invitation = Invitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            throw new InvitationException(InvitationException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data'    => ['email' => $invitation->email, 'role' => $invitation->role],
        ]);
    }
}
