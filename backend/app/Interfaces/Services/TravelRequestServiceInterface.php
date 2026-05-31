<?php

declare(strict_types=1);

namespace App\Interfaces\Services;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface TravelRequestServiceInterface
{
    public function getAllTravelRequests(Request $request): LengthAwarePaginator;

    public function getTravelRequest(int $id): ?TravelRequest;

    public function createTravelRequest(array $data): TravelRequest;

    public function updateTravelRequest(TravelRequest $travelRequest, array $data): TravelRequest;

    public function updateStatus(TravelRequest $travelRequest, string $newStatus, User $user): TravelRequest;

    public function cancelRequest(TravelRequest $travelRequest, User $user, string $reason): TravelRequest;

    public function canViewTravelRequest(TravelRequest $travelRequest, User $user): bool;

    public function canUpdateTravelRequest(TravelRequest $travelRequest, User $user): bool;

    public function canModifyTravelRequest(TravelRequest $travelRequest): bool;

    public function canUpdateStatus(TravelRequest $travelRequest, User $user): bool;

    public function canBeCancelled(TravelRequest $travelRequest): bool;
}
