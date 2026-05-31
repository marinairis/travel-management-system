<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\TravelRequest;

use App\Exceptions\TravelRequest\TravelRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\UpdateTravelRequestRequest;
use App\Interfaces\Services\TravelRequestServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateTravelRequestController extends Controller
{
    public function __construct(
        private readonly TravelRequestServiceInterface $service
    ) {}

    public function __invoke(UpdateTravelRequestRequest $request, int $id): JsonResponse
    {
        try {
            $travelRequest = $this->service->getTravelRequest($id);

            if (!$travelRequest) {
                throw new TravelRequestException(TravelRequestException::NOT_FOUND, Response::HTTP_NOT_FOUND);
            }

            if (!$this->service->canUpdateTravelRequest($travelRequest, Auth::user())) {
                throw new TravelRequestException(TravelRequestException::UNAUTHORIZED, Response::HTTP_FORBIDDEN);
            }

            if (!$this->service->canModifyTravelRequest($travelRequest)) {
                throw new TravelRequestException(TravelRequestException::NOT_EDITABLE, Response::HTTP_FORBIDDEN);
            }

            $updated = $this->service->updateTravelRequest($travelRequest, $request->validated());

            return response()->json([
                'success' => true,
                'message' => __('messages.travel_request.updated'),
                'data'    => $updated,
            ]);
        } catch (TravelRequestException $e) {
            return response()->json([
                'success' => false,
                'message' => __("messages.{$e->getMessage()}"),
            ], $e->getStatusCode());
        }
    }
}
