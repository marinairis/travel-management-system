<?php

declare(strict_types=1);

namespace App\Docs;

/**
 * @OA\Get(
 *     path="/api/notifications",
 *     tags={"Notifications"},
 *     summary="List the last 50 notifications for the authenticated user",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Notifications list",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object",
 *                 @OA\Property(property="id", type="string"),
 *                 @OA\Property(property="data", type="object"),
 *                 @OA\Property(property="read_at", type="string", format="date-time", nullable=true),
 *                 @OA\Property(property="created_at", type="string", format="date-time")
 *             )),
 *             @OA\Property(property="unread_count", type="integer")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Patch(
 *     path="/api/notifications/read-all",
 *     tags={"Notifications"},
 *     summary="Mark all unread notifications as read",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="All notifications marked as read",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Patch(
 *     path="/api/notifications/{id}/read",
 *     tags={"Notifications"},
 *     summary="Mark a single notification as read",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, description="Notification UUID", @OA\Schema(type="string")),
 *     @OA\Response(
 *         response=200,
 *         description="Notification marked as read",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=404, description="Notification not found")
 * )
 */
abstract class NotificationDoc {}
