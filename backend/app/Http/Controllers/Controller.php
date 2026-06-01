<?php

declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Travel Management API",
 *     description="API REST para gerenciamento de pedidos de viagem corporativa. Autenticação via JWT Bearer token.",
 *     @OA\Contact(email="marinairis.dev@gmail.com")
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Auth", description="Autenticação e gerenciamento de sessão")
 * @OA\Tag(name="Travel Requests", description="Pedidos de viagem corporativa")
 * @OA\Tag(name="Users", description="Gerenciamento de usuários (admin)")
 * @OA\Tag(name="Locations", description="Cidades e destinos (IBGE)")
 */
abstract class Controller
{
}
