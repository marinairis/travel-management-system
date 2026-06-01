<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\User;

use App\Exceptions\User\UserException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserFormRequest;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $service
    ) {}

    public function __invoke(UserFormRequest $request, int $id): JsonResponse
    {
        $user = $this->service->findById($id);

        if (!$user) {
            throw new UserException(UserException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $updated = $this->service->updateUser($user, $request->only(['name', 'role']));

        return response()->json([
            'success' => true,
            'message' => __('messages.user.updated'),
            'data'    => $updated,
        ]);
    }
}
