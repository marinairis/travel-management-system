<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;

class BasicListUsersController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $service
    ) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->basicList(),
        ]);
    }
}
