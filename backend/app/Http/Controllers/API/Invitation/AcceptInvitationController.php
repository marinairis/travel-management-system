<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Invitation;

use App\Exceptions\Invitation\InvitationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invitation\AcceptInvitationRequest;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AcceptInvitationController extends Controller
{
    public function __invoke(AcceptInvitationRequest $request, string $token): JsonResponse
    {
        try {
            $invitation = Invitation::where('token', $token)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->first();

            if (!$invitation) {
                throw new InvitationException(InvitationException::NOT_FOUND, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $invitation->email,
                'password' => Hash::make($request->password),
                'role'     => $invitation->role,
            ]);

            $invitation->accepted_at = now();
            $invitation->save();

            $jwtToken = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => __('messages.invitation.accepted'),
                'data'    => ['user' => $user, 'token' => $jwtToken, 'token_type' => 'bearer'],
            ], 201);
        } catch (InvitationException $e) {
            return response()->json([
                'success' => false,
                'message' => __("messages.{$e->getMessage()}"),
            ], $e->getStatusCode());
        }
    }
}
