<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TravelRequestStatus;
use App\Interfaces\Repositories\TravelRequestRepositoryInterface;
use App\Interfaces\Services\TravelRequestServiceInterface;
use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use App\Traits\HasActivityLogging;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TravelRequestService implements TravelRequestServiceInterface
{
    use HasActivityLogging;

    public function __construct(
        private readonly TravelRequestRepositoryInterface $repository
    ) {}

    public function getAllTravelRequests(Request $request): LengthAwarePaginator
    {
        $filters = $request->only(['status', 'destination', 'start_date', 'end_date']);
        $perPage = (int) $request->input('per_page', 10);

        return $this->repository->findAllPaginated(Auth::user(), $filters, $perPage);
    }

    public function getTravelRequest(int $id): ?TravelRequest
    {
        return $this->repository->findById($id);
    }

    public function createTravelRequest(array $data): TravelRequest
    {
        $travelRequest = $this->repository->create([
            'user_id' => Auth::id(),
            'requester_name' => $data['requester_name'],
            'destination' => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'notes' => $data['notes'] ?? null,
            'travel_type' => $data['travel_type'] ?? null,
            'status' => TravelRequestStatus::Requested->value,
        ]);

        $this->logActivityCreate($travelRequest);

        return $travelRequest->load(['user', 'approvedBy']);
    }

    public function updateTravelRequest(TravelRequest $travelRequest, array $data): TravelRequest
    {
        $oldValues = $travelRequest->toArray();

        $travelRequest->update([
            'requester_name' => $data['requester_name'],
            'destination' => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'notes' => $data['notes'] ?? null,
            'travel_type' => $data['travel_type'] ?? null,
        ]);

        $this->logActivityUpdate($travelRequest, $oldValues);

        return $travelRequest->fresh()->load(['user', 'approvedBy']);
    }

    public function updateStatus(TravelRequest $travelRequest, string $newStatus, User $user): TravelRequest
    {
        $oldStatus = $travelRequest->status;

        $travelRequest->status = $newStatus;

        if ($newStatus === TravelRequestStatus::Approved->value) {
            $travelRequest->approved_by = $user->id;
            $travelRequest->approved_at = now();
        }

        $updated = $this->repository->save($travelRequest);

        Notification::send($travelRequest->user, new TravelRequestStatusChanged($updated, $oldStatus));

        $this->logActivityStatusChange($updated, $oldStatus, $newStatus);

        return $updated;
    }

    public function cancelRequest(TravelRequest $travelRequest, User $user, string $reason): TravelRequest
    {
        $oldStatus = $travelRequest->status;

        $travelRequest->status = TravelRequestStatus::Cancelled->value;
        $travelRequest->cancel_reason = $reason;
        $travelRequest->cancelled_by = $user->id;
        $travelRequest->cancelled_at = now();

        $updated = $this->repository->save($travelRequest);

        Notification::send($travelRequest->user, new TravelRequestStatusChanged($updated, $oldStatus));

        $this->logActivityStatusChange($updated, $oldStatus, TravelRequestStatus::Cancelled->value);

        return $updated;
    }

    public function canViewTravelRequest(TravelRequest $travelRequest, User $user): bool
    {
        return $user->isApprover() || $travelRequest->user_id === $user->id;
    }

    public function canUpdateTravelRequest(TravelRequest $travelRequest, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $travelRequest->user_id === $user->id;
    }

    public function canModifyTravelRequest(TravelRequest $travelRequest): bool
    {
        return $travelRequest->status !== TravelRequestStatus::Approved->value;
    }

    public function canUpdateStatus(TravelRequest $travelRequest, User $user): bool
    {
        if ($travelRequest->user_id === $user->id) {
            return false;
        }

        return $user->isApprover();
    }

    public function canBeCancelled(TravelRequest $travelRequest): bool
    {
        $status = TravelRequestStatus::from($travelRequest->status);

        if ($status->isFinal()) {
            return false;
        }

        return $travelRequest->departure_date >= now()->startOfDay();
    }
}
