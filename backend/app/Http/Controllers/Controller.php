<?php

declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Travel Management API",
 *     description="REST API for corporate travel request management. Authentication via JWT Bearer token.",
 *     @OA\Contact(email="marinairis.dev@gmail.com")
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Local server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Auth", description="Authentication and session management")
 * @OA\Tag(name="Travel Requests", description="Corporate travel requests")
 * @OA\Tag(name="Users", description="User management (admin only)")
 * @OA\Tag(name="Locations", description="Cities and destinations (IBGE)")
 * @OA\Tag(name="Dashboard", description="Dashboard statistics and summaries")
 * @OA\Tag(name="Invitations", description="User invitation flow")
 * @OA\Tag(name="Activity Logs", description="Audit trail of user actions")
 * @OA\Tag(name="Notifications", description="In-app notifications")
 */
abstract class Controller
{
}
