<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\TravelRequest;

use App\Enums\TravelRequestStatus;
use App\Exceptions\TravelRequest\TravelRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\CancelTravelRequestRequest;
use App\Interfaces\Services\TravelRequestServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CancelTravelRequestController extends Controller
{
    public function __construct(
        private readonly TravelRequestServiceInterface $service
    ) {}

    public function __invoke(CancelTravelRequestRequest $request, int $id): JsonResponse
    {
        $user          = Auth::user();
        $travelRequest = $this->service->getTravelRequest($id);

        if (!$travelRequest) {
            throw new TravelRequestException(TravelRequestException::NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if ($travelRequest->user_id !== $user->id && !$user->isApprover()) {
            throw new TravelRequestException(TravelRequestException::UNAUTHORIZED, Response::HTTP_FORBIDDEN);
        }

        if (TravelRequestStatus::from($travelRequest->status) === TravelRequestStatus::Cancelled) {
            throw new TravelRequestException(TravelRequestException::ALREADY_CANCELLED, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (TravelRequestStatus::from($travelRequest->status) === TravelRequestStatus::Expired) {
            throw new TravelRequestException(TravelRequestException::ALREADY_EXPIRED, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$this->service->canBeCancelled($travelRequest)) {
            throw new TravelRequestException(TravelRequestException::NOT_CANCELLABLE, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cancelled = $this->service->cancelRequest($travelRequest, $user, $request->input('reason', ''));

        return response()->json([
            'success' => true,
            'message' => __('messages.travel_request.cancelled'),
            'data'    => $cancelled,
        ]);
    }
}
