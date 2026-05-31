<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\TravelRequest;

use App\Exceptions\TravelRequest\TravelRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\TravelRequestStatusRequest;
use App\Interfaces\Services\TravelRequestServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateTravelRequestStatusController extends Controller
{
    public function __construct(
        private readonly TravelRequestServiceInterface $service
    ) {}

    public function __invoke(TravelRequestStatusRequest $request, int $id): JsonResponse
    {
        try {
            $travelRequest = $this->service->getTravelRequest($id);

            if (!$travelRequest) {
                throw new TravelRequestException(TravelRequestException::NOT_FOUND, Response::HTTP_NOT_FOUND);
            }

            if (!$this->service->canUpdateStatus($travelRequest, Auth::user())) {
                throw new TravelRequestException(TravelRequestException::CANNOT_CHANGE_OWN_STATUS, Response::HTTP_FORBIDDEN);
            }

            $updated = $this->service->updateStatus($travelRequest, $request->status, Auth::user());

            return response()->json([
                'success' => true,
                'message' => __('messages.travel_request.status_updated'),
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
