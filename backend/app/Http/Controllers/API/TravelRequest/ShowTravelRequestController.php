<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\TravelRequest;

use App\Exceptions\TravelRequest\TravelRequestException;
use App\Http\Controllers\Controller;
use App\Interfaces\Services\TravelRequestServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ShowTravelRequestController extends Controller
{
    public function __construct(
        private readonly TravelRequestServiceInterface $service
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $travelRequest = $this->service->getTravelRequest($id);

        if (! $travelRequest) {
            throw new TravelRequestException(TravelRequestException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if (! $this->service->canViewTravelRequest($travelRequest, Auth::user())) {
            throw new TravelRequestException(TravelRequestException::UNAUTHORIZED, Response::HTTP_FORBIDDEN);
        }

        return response()->json(['success' => true, 'message' => __('messages.general.success'), 'data' => $travelRequest]);
    }
}
