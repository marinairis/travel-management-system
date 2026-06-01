<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Exceptions\User\UserException;
use App\Http\Controllers\Controller;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ToggleUserStatusController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $service
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $user = $this->service->findById($id, withTrashed: true);

        if (! $user) {
            throw new UserException(UserException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if ($user->id === Auth::id()) {
            throw new UserException(UserException::CANNOT_DISABLE_SELF, Response::HTTP_FORBIDDEN);
        }

        $result = $this->service->toggleStatus($user);

        $messageKey = $result['action'] === 'activated'
            ? 'messages.user.activated'
            : 'messages.user.deactivated';

        return response()->json([
            'success' => true,
            'message' => __($messageKey),
            'data' => $result['user'],
        ]);
    }
}
