<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\TravelRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\TravelRequestFilterRequest;
use App\Interfaces\Services\TravelRequestServiceInterface;
use Illuminate\Http\JsonResponse;

class ListTravelRequestsController extends Controller
{
    public function __construct(
        private readonly TravelRequestServiceInterface $service
    ) {}

    public function __invoke(TravelRequestFilterRequest $request): JsonResponse
    {
        $travelRequests = $this->service->getAllTravelRequests($request);

        return response()->json([
            'success' => true,
            'message' => __('messages.general.success'),
            'data'    => $travelRequests->items(),
            'meta'    => [
                'current_page' => $travelRequests->currentPage(),
                'last_page'    => $travelRequests->lastPage(),
                'per_page'     => $travelRequests->perPage(),
                'total'        => $travelRequests->total(),
            ],
        ]);
    }
}
