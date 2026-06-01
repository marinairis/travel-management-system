<?php

declare(strict_types=1);

namespace App\Docs;

/**
 * @OA\Get(
 *     path="/api/users",
 *     tags={"Users"},
 *     summary="List all users with optional filters and pagination (admin only)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="user_type", in="query", required=false, @OA\Schema(type="string", enum={"admin","manager","requester","basic"})),
 *     @OA\Parameter(name="email", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"active","inactive"})),
 *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100, default=10)),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Paginated list of users",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="current_page", type="integer"),
 *                 @OA\Property(property="last_page", type="integer"),
 *                 @OA\Property(property="per_page", type="integer"),
 *                 @OA\Property(property="total", type="integer")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 *
 * @OA\Get(
 *     path="/api/users/basic",
 *     tags={"Users"},
 *     summary="Return a minimal list of users (id, name, email) for dropdowns",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Basic user list",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/api/users/{id}",
 *     tags={"Users"},
 *     summary="Get a user by ID (own profile or approver access)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\Response(
 *         response=200,
 *         description="User data",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Put(
 *     path="/api/users/{id}",
 *     tags={"Users"},
 *     summary="Update a user's name or role (admin only)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="role", type="string", enum={"requester","manager","admin"})
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="User updated",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Delete(
 *     path="/api/users/{id}",
 *     tags={"Users"},
 *     summary="Delete a user (admin only, cannot delete self)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\Response(
 *         response=200,
 *         description="User deleted",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden or cannot delete self"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Patch(
 *     path="/api/users/{id}/toggle-status",
 *     tags={"Users"},
 *     summary="Activate or deactivate a user (admin only, cannot toggle self)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\Response(
 *         response=200,
 *         description="User status toggled",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden or cannot toggle self"),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Get(
 *     path="/api/users/{id}/pending-requests-count",
 *     tags={"Users"},
 *     summary="Return the number of pending travel requests for a user (admin only)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Pending request count",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="count", type="integer")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Not found")
 * )
 */
abstract class UserDoc {}
