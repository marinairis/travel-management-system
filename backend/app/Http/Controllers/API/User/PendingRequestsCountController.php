<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Exceptions\User\UserException;
use App\Http\Controllers\Controller;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PendingRequestsCountController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $service
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $user = $this->service->findById($id);

        if (!$user) {
            throw new UserException(UserException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data'    => ['count' => $this->service->pendingRequestsCount($id)],
        ]);
    }
}
