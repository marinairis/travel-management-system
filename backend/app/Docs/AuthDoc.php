<?php

declare(strict_types=1);

namespace App\Docs;

/**
 * @OA\Post(
 *     path="/api/login",
 *     tags={"Auth"},
 *     summary="Authenticate user and return JWT token",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"email","password"},
 *
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="Secret@123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user", type="object"),
 *                 @OA\Property(property="token", type="string"),
 *                 @OA\Property(property="token_type", type="string", example="bearer")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Invalid credentials"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *     path="/api/logout",
 *     tags={"Auth"},
 *     summary="Invalidate the current JWT token",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Logout successful",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/api/me",
 *     tags={"Auth"},
 *     summary="Return the authenticated user's data",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Authenticated user data",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Post(
 *     path="/api/refresh",
 *     tags={"Auth"},
 *     summary="Refresh the JWT token",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Token refreshed",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="token", type="string"),
 *                 @OA\Property(property="token_type", type="string", example="bearer")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Invalid or expired token")
 * )
 *
 * @OA\Post(
 *     path="/api/forgot-password",
 *     tags={"Auth"},
 *     summary="Send a password reset link to the user's email",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"email"},
 *
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Reset link sent",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *
 *     @OA\Response(response=422, description="Validation error"),
 *     @OA\Response(response=500, description="Failed to send email")
 * )
 *
 * @OA\Post(
 *     path="/api/reset-password",
 *     tags={"Auth"},
 *     summary="Reset the user's password using a token",
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"email","password","password_confirmation","token"},
 *
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="token", type="string"),
 *             @OA\Property(property="password", type="string", format="password", example="NewSecret@123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="NewSecret@123")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Password reset successfully",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *
 *     @OA\Response(response=400, description="Invalid token, user or throttled"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
abstract class AuthDoc {}
