<?php

declare(strict_types=1);

namespace App\Docs;

/**
 * @OA\Get(
 *     path="/api/locations/destinations",
 *     tags={"Locations"},
 *     summary="Return the list of popular/cached destinations (public endpoint)",
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of destinations",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="total", type="integer"),
 *                 @OA\Property(property="cached", type="boolean")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=500, description="Server error")
 * )
 *
 * @OA\Get(
 *     path="/api/locations/cities",
 *     tags={"Locations"},
 *     summary="Search cities by name (IBGE dataset)",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="q", in="query", required=false, description="Search term", @OA\Schema(type="string", maxLength=100)),
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of matching cities",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="total", type="integer"),
 *                 @OA\Property(property="has_query", type="boolean")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=500, description="Server error")
 * )
 */
abstract class LocationDoc {}
