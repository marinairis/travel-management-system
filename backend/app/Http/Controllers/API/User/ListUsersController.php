<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserFilterRequest;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;

class ListUsersController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $service
    ) {}

    public function __invoke(UserFilterRequest $request): JsonResponse
    {
        $users = $this->service->getAllUsers(
            $request->only(['user_type', 'email', 'status']),
            (int) $request->input('per_page', 10)
        );

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }
}
