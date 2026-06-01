<?php

declare(strict_types=1);

namespace App\Docs;

/**
 * @OA\Get(
 *     path="/api/travel-requests",
 *     tags={"Travel Requests"},
 *     summary="List travel requests with optional filters and pagination",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"requested","approved","cancelled","expired"})),
 *     @OA\Parameter(name="destination", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="start_date", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="end_date", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100, default=10)),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Paginated list of travel requests",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
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
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Post(
 *     path="/api/travel-requests",
 *     tags={"Travel Requests"},
 *     summary="Create a new travel request",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"requester_name","destination","departure_date","return_date"},
 *
 *             @OA\Property(property="requester_name", type="string", example="John Doe"),
 *             @OA\Property(property="destination", type="string", example="São Paulo, SP"),
 *             @OA\Property(property="departure_date", type="string", format="date", example="2026-07-10"),
 *             @OA\Property(property="return_date", type="string", format="date", example="2026-07-15"),
 *             @OA\Property(property="travel_type", type="string", enum={"bus","plane","car","hotel"}, nullable=true),
 *             @OA\Property(property="notes", type="string", nullable=true)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Travel request created",
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
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Get(
 *     path="/api/travel-requests/{id}",
 *     tags={"Travel Requests"},
 *     summary="Get a single travel request by ID",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Travel request data",
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
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Put(
 *     path="/api/travel-requests/{id}",
 *     tags={"Travel Requests"},
 *     summary="Update a travel request (only when status is 'requested')",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="requester_name", type="string"),
 *             @OA\Property(property="destination", type="string"),
 *             @OA\Property(property="departure_date", type="string", format="date"),
 *             @OA\Property(property="return_date", type="string", format="date"),
 *             @OA\Property(property="travel_type", type="string", enum={"bus","plane","car","hotel"}, nullable=true),
 *             @OA\Property(property="notes", type="string", nullable=true)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Travel request updated",
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
 *     @OA\Response(response=403, description="Forbidden or not editable"),
 *     @OA\Response(response=404, description="Not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 *
 * @OA\Patch(
 *     path="/api/travel-requests/{id}/cancel",
 *     tags={"Travel Requests"},
 *     summary="Cancel a travel request",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\RequestBody(
 *         required=false,
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="reason", type="string", nullable=true, maxLength=500)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Travel request cancelled",
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
 *     @OA\Response(response=422, description="Already cancelled, expired or not cancellable")
 * )
 *
 * @OA\Patch(
 *     path="/api/travel-requests/{id}/status",
 *     tags={"Travel Requests"},
 *     summary="Approve or cancel a travel request (manager or admin only)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"status"},
 *
 *             @OA\Property(property="status", type="string", enum={"approved","cancelled"})
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Status updated",
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
 *     @OA\Response(response=403, description="Forbidden or cannot change own request status"),
 *     @OA\Response(response=404, description="Not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
abstract class TravelRequestDoc {}
