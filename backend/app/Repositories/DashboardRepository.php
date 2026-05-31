<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\DashboardRepositoryInterface;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getStats(User $user): array
    {
        $baseQuery = fn () => $user->isApprover()
            ? TravelRequest::query()
            : TravelRequest::where('user_id', $user->id);

        $total = $baseQuery()->count();

        $byStatus = $baseQuery()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $byTravelType = $baseQuery()
            ->selectRaw('travel_type, count(*) as total')
            ->whereNotNull('travel_type')
            ->groupBy('travel_type')
            ->pluck('total', 'travel_type')
            ->toArray();

        $topDestinations = $baseQuery()
            ->selectRaw('destination, count(*) as total')
            ->groupBy('destination')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['name' => $row->destination, 'count' => $row->total])
            ->toArray();

        return compact('total', 'byStatus', 'byTravelType', 'topDestinations');
    }
}
