<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Dashboard;

use App\Interfaces\Services\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GetPendingApprovalController
{
    public function __construct(
        private readonly DashboardServiceInterface $service
    ) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getOldestPendingApproval(Auth::user()),
        ]);
    }
}
