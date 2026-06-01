<?php

declare(strict_types=1);

namespace App\Docs;

/**
 * @OA\Get(
 *     path="/api/invitations/{token}",
 *     tags={"Invitations"},
 *     summary="Retrieve a valid (non-accepted, non-expired) invitation by token",
 *     @OA\Parameter(name="token", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\Response(
 *         response=200,
 *         description="Invitation data",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="email", type="string"),
 *                 @OA\Property(property="role", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Invitation not found, already accepted or expired")
 * )
 *
 * @OA\Post(
 *     path="/api/invitations/{token}/accept",
 *     tags={"Invitations"},
 *     summary="Accept an invitation and create the user account",
 *     @OA\Parameter(name="token", in="path", required=true, @OA\Schema(type="string")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","password","password_confirmation"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="password", type="string", format="password", example="Secret@123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="Secret@123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Invitation accepted and user created",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user", type="object"),
 *                 @OA\Property(property="token", type="string"),
 *                 @OA\Property(property="token_type", type="string", example="bearer")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=422, description="Invitation not found or validation error")
 * )
 *
 * @OA\Post(
 *     path="/api/users/invite",
 *     tags={"Invitations"},
 *     summary="Send an invitation email to a new user (admin only)",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","role"},
 *             @OA\Property(property="email", type="string", format="email", example="newuser@example.com"),
 *             @OA\Property(property="role", type="string", enum={"requester","manager","admin"})
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Invitation sent",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=422, description="Validation error or email already in use"),
 *     @OA\Response(response=500, description="Failed to send email")
 * )
 */
abstract class InvitationDoc {}
