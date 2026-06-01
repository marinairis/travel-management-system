<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\TravelRequestRepositoryInterface;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TravelRequestRepository implements TravelRequestRepositoryInterface
{
    public function findById(int $id): ?TravelRequest
    {
        return TravelRequest::with(['user', 'approvedBy', 'cancelledBy'])->find($id);
    }

    public function findAllPaginated(User $user, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $user->isApprover()
            ? TravelRequest::with(['user', 'approvedBy', 'cancelledBy'])
            : TravelRequest::where('user_id', $user->id)->with(['user', 'approvedBy', 'cancelledBy']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): TravelRequest
    {
        return TravelRequest::create($data);
    }

    public function save(TravelRequest $travelRequest): TravelRequest
    {
        $travelRequest->save();

        return $travelRequest->fresh()->load(['user', 'approvedBy', 'cancelledBy']);
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (! empty($filters['destination'])) {
            $query->byDestination($filters['destination']);
        }

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }
    }
}
