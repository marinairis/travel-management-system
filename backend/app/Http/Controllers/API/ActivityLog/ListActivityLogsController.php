<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\ActivityLog;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityLog\ActivityLogFilterRequest;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ListActivityLogsController extends Controller
{
    public function __invoke(ActivityLogFilterRequest $request): JsonResponse
    {
        $user = Auth::user();
        $query = ActivityLog::with(['user' => fn ($userQuery) => $userQuery->select('id', 'name', 'email', 'role')])
            ->orderBy('created_at', 'desc');

        if ($user->isRequester()) {
            $query->where('user_id', $user->id);
        }

        $this->applyFilters($query, $request);

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->get('per_page', 10)),
        ]);
    }

    private function applyFilters($query, ActivityLogFilterRequest $request): void
    {
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('model_id')) {
            $query->where('model_id', $request->model_id);
        }
    }
}
