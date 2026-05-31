<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\User;
use App\Models\ActivityLog;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TravelRequestService
{
    /**
     * Get all travel requests based on user role
     */
    public function getAllTravelRequests(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->isApprover()
            ? TravelRequest::with(['user', 'approvedBy', 'cancelledBy'])
            : TravelRequest::where('user_id', $user->id)->with(['user', 'approvedBy', 'cancelledBy']);

        $this->applyFilters($query, $request);

        $perPage = $request->input('per_page', 10);
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new travel request
     */
    public function createTravelRequest(array $data): TravelRequest
    {
        $travelRequest = TravelRequest::create([
            'user_id' => Auth::id(),
            'requester_name' => $data['requester_name'],
            'destination' => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'notes' => $data['notes'] ?? null,
            'travel_type' => $data['travel_type'] ?? null,
            'status' => 'requested',
        ]);

        $this->logActivity(
            'create',
            $travelRequest,
            'Pedido de viagem criado',
            null,
            $travelRequest->toArray()
        );

        return $travelRequest->load(['user', 'approvedBy']);
    }

    /**
     * Get a single travel request by ID
     */
    public function getTravelRequest(int $id): ?TravelRequest
    {
        return TravelRequest::with(['user', 'approvedBy', 'cancelledBy'])->find($id);
    }

    /**
     * Update travel request data
     */
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

        $this->logActivity(
            'update',
            $travelRequest,
            'Pedido de viagem atualizado',
            $oldValues,
            $travelRequest->fresh()->toArray()
        );

        return $travelRequest->fresh()->load(['user', 'approvedBy']);
    }

    /**
     * Update travel request status (approve/reject)
     */
    public function updateStatus(TravelRequest $travelRequest, string $newStatus, User $user): TravelRequest
    {
        $oldStatus = $travelRequest->status;
        
        $travelRequest->status = $newStatus;
        
        if ($newStatus === 'approved') {
            $travelRequest->approved_by = $user->id;
            $travelRequest->approved_at = now();
        }
        
        $travelRequest->save();

        // Send notification to requester
        Notification::send(
            $travelRequest->user,
            new TravelRequestStatusChanged($travelRequest, $oldStatus)
        );

        $this->logActivityStatusChange(
            $travelRequest,
            $oldStatus,
            $newStatus,
            "Status alterado de {$this->translateStatus($oldStatus)} para {$this->translateStatus($newStatus)}"
        );

        return $travelRequest->fresh()->load(['user', 'approvedBy']);
    }

    /**
     * Cancel a travel request
     */
    public function cancelRequest(TravelRequest $travelRequest, User $user, string $reason = ''): TravelRequest
    {
        $oldStatus = $travelRequest->status;
        
        $travelRequest->status = 'cancelled';
        $travelRequest->cancel_reason = $reason;
        $travelRequest->cancelled_by = $user->id;
        $travelRequest->cancelled_at = now();
        $travelRequest->save();

        // Send notification to requester
        Notification::send(
            $travelRequest->user,
            new TravelRequestStatusChanged($travelRequest, $oldStatus)
        );

        $this->logActivityStatusChange(
            $travelRequest,
            $oldStatus,
            'cancelled',
            'Pedido de viagem cancelado'
        );

        return $travelRequest->fresh()->load(['user', 'approvedBy', 'cancelledBy']);
    }

    /**
     * Validate if user can view the travel request
     */
    public function canViewTravelRequest(TravelRequest $travelRequest, User $user): bool
    {
        return $user->isApprover() || $travelRequest->user_id === $user->id;
    }

    /**
     * Validate if user can update the travel request
     */
    public function canUpdateTravelRequest(TravelRequest $travelRequest, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $travelRequest->user_id === $user->id;
    }

    /**
     * Validate if travel request can be modified
     */
    public function canModifyTravelRequest(TravelRequest $travelRequest): bool
    {
        return $travelRequest->status !== 'approved';
    }

    /**
     * Validate if user can update the status
     */
    public function canUpdateStatus(TravelRequest $travelRequest, User $user): bool
    {
        // User cannot change their own request status
        if ($travelRequest->user_id === $user->id) {
            return false;
        }

        // Only approvers can update status
        return $user->isApprover();
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('status') && $request->status) {
            $query->byStatus($request->status);
        }

        if ($request->has('destination') && $request->destination) {
            $query->byDestination($request->destination);
        }

        if (
            $request->has('start_date') && $request->has('end_date') &&
            $request->start_date && $request->end_date
        ) {
            $query->byDateRange($request->start_date, $request->end_date);
        }
    }

    /**
     * Check if request can be cancelled based on business rules
     */
    public function canBeCancelled(TravelRequest $travelRequest): bool
    {
        if ($travelRequest->status === 'cancelled') {
            return false;
        }

        if ($travelRequest->status === 'expired') {
            return false;
        }

        return $travelRequest->departure_date >= now()->startOfDay();
    }

    /**
     * Log an activity
     */
    private function logActivity(
        string $action,
        TravelRequest $model,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log status change activity
     */
    private function logActivityStatusChange(
        TravelRequest $model,
        string $oldStatus,
        string $newStatus,
        string $description
    ): void {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'status_change',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => $description,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Translate status to Portuguese
     */
    private function translateStatus(string $status): string
    {
        $translations = [
            'requested' => 'Solicitado',
            'approved' => 'Aprovado',
            'cancelled' => 'Cancelado',
            'expired' => 'Vencido',
        ];

        return $translations[$status] ?? $status;
    }
}