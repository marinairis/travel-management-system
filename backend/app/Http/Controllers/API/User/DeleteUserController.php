<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Exceptions\User\UserException;
use App\Http\Controllers\Controller;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DeleteUserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $service
    ) {}

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $user = $this->service->findById($id);

        if (!$user) {
            throw new UserException(UserException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if ($user->id === Auth::id()) {
            throw new UserException(UserException::CANNOT_DELETE_SELF, Response::HTTP_FORBIDDEN);
        }

        $this->service->deleteUser($user);

        return response()->json([
            'success' => true,
            'message' => __('messages.user.deleted'),
        ]);
    }
}
