<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Exceptions\User\UserException;
use App\Http\Controllers\Controller;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ShowUserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $service
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $user = $this->service->findById($id);
        $currentUser = Auth::user();

        if (! $user) {
            throw new UserException(UserException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if (! $currentUser->isApprover() && $user->id !== $currentUser->id) {
            throw new UserException('user.cannot_view', Response::HTTP_FORBIDDEN);
        }

        return response()->json(['success' => true, 'data' => $user]);
    }
}
