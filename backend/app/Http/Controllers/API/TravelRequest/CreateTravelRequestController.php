<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\TravelRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\CreateTravelRequestRequest;
use App\Interfaces\Services\TravelRequestServiceInterface;
use Illuminate\Http\JsonResponse;

class CreateTravelRequestController extends Controller
{
    public function __construct(
        private readonly TravelRequestServiceInterface $service
    ) {}

    public function __invoke(CreateTravelRequestRequest $request): JsonResponse
    {
        $travelRequest = $this->service->createTravelRequest($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('messages.travel_request.created'),
            'data' => $travelRequest,
        ], 201);
    }
}
