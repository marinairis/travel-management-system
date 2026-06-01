<?php

declare(strict_types=1);

namespace App\Docs;

/**
 * @OA\Get(
 *     path="/api/activity-logs",
 *     tags={"Activity Logs"},
 *     summary="List activity logs (requesters see own logs only; managers and admins see all)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="user_id", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
 *     @OA\Parameter(name="action", in="query", required=false, @OA\Schema(type="string", maxLength=100)),
 *     @OA\Parameter(name="model_type", in="query", required=false, @OA\Schema(type="string", maxLength=100)),
 *     @OA\Parameter(name="model_id", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
 *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100, default=10)),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Paginated activity logs",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */
abstract class ActivityLogDoc {}
