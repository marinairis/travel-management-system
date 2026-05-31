<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\DashboardRepositoryInterface;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DashboardRepository implements DashboardRepositoryInterface
{
    private const CARD_COLUMNS = ['id', 'user_id', 'requester_name', 'destination', 'departure_date', 'travel_type', 'status', 'created_at'];

    public function getStats(User $user): array
    {
        $total = $this->baseQuery($user)->count();

        $byStatus = $this->baseQuery($user)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $byTravelType = $this->baseQuery($user)
            ->selectRaw('travel_type, count(*) as total')
            ->whereNotNull('travel_type')
            ->groupBy('travel_type')
            ->pluck('total', 'travel_type')
            ->toArray();

        $topDestinations = $this->baseQuery($user)
            ->selectRaw('destination, count(*) as total')
            ->groupBy('destination')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['name' => $row->destination, 'count' => $row->total])
            ->toArray();

        return compact('total', 'byStatus', 'byTravelType', 'topDestinations');
    }

    public function getOldestPendingApproval(User $user): array
    {
        return $this->baseQuery($user)
            ->where('status', 'requested')
            ->orderBy('created_at')
            ->limit(10)
            ->get(self::CARD_COLUMNS)
            ->toArray();
    }

    public function getRecentRequests(User $user): array
    {
        return $this->baseQuery($user)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(self::CARD_COLUMNS)
            ->toArray();
    }

    private function baseQuery(User $user): Builder
    {
        return $user->isApprover()
            ? TravelRequest::query()
            : TravelRequest::where('user_id', $user->id);
    }
}
