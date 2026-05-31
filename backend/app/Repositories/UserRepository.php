<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\TravelRequestStatus;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id, bool $withTrashed = false): ?User
    {
        $query = $withTrashed ? User::withTrashed() : User::query();
        return $query->withCount('travelRequests')->find($id);
    }

    public function findAllPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = User::withTrashed()->withCount('travelRequests');

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function basicList(): Collection
    {
        return User::select('id', 'name')->orderBy('name')->get();
    }

    public function cancelOpenRequests(User $user, string $reason): int
    {
        $requests = TravelRequest::where('user_id', $user->id)
            ->whereIn('status', [
                TravelRequestStatus::Requested->value,
                TravelRequestStatus::Approved->value,
            ])
            ->get();

        foreach ($requests as $request) {
            $request->status        = TravelRequestStatus::Cancelled->value;
            $request->cancel_reason = $reason;
            $request->cancelled_by  = Auth::id();
            $request->cancelled_at  = now();
            $request->save();
        }

        return $requests->count();
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['user_type'])) {
            $type = $filters['user_type'];
            if ($type === 'basic') {
                $query->where('role', '!=', 'admin');
            } else {
                $query->where('role', $type);
            }
        }

        $status = $filters['status'] ?? null;
        if ($status === 'active') {
            $query->where('is_active', true)->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->where(function ($q) {
                $q->where('is_active', false)->orWhereNotNull('deleted_at');
            });
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }
    }
}
