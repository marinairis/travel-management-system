<?php

declare(strict_types=1);

namespace App\Docs;

/**
 * @OA\Get(
 *     path="/api/dashboard/stats",
 *     tags={"Dashboard"},
 *     summary="Get dashboard statistics for the authenticated user",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Dashboard statistics",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="total", type="integer"),
 *                 @OA\Property(property="by_status", type="object"),
 *                 @OA\Property(property="by_travel_type", type="object"),
 *                 @OA\Property(property="top_destinations", type="array", @OA\Items(type="object"))
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/api/dashboard/pending-approval",
 *     tags={"Dashboard"},
 *     summary="Get the oldest travel request pending approval",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Oldest pending approval request",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object", nullable=true)
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/api/dashboard/recent-requests",
 *     tags={"Dashboard"},
 *     summary="Get the most recent travel requests for the authenticated user",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Recent travel requests",
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
 */
abstract class DashboardDoc {}
