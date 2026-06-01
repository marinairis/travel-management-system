<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Dashboard;

use App\Interfaces\Services\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GetDashboardStatsController
{
    public function __construct(
        private readonly DashboardServiceInterface $service
    ) {}

    public function __invoke(): JsonResponse
    {
        $stats = $this->service->getStats(Auth::user());

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $stats['total'],
                'by_status' => $stats['byStatus'],
                'by_travel_type' => $stats['byTravelType'],
                'top_destinations' => $stats['topDestinations'],
            ],
        ]);
    }
}
