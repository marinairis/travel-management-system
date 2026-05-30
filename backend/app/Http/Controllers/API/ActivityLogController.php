<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($user->isRequester()) {
            $query->where('user_id', $user->id);
        }

        $this->applyFilters($query, $request);

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    private function applyFilters($query, Request $request)
    {
        $this->filterByUser($query, $request);
        $this->filterByAction($query, $request);
        $this->filterByModelType($query, $request);
        $this->filterByModelId($query, $request);
    }

    private function filterByUser($query, Request $request)
    {
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
    }

    private function filterByAction($query, Request $request)
    {
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }
    }

    private function filterByModelType($query, Request $request)
    {
        if ($request->has('model_type') && $request->model_type) {
            $query->where('model_type', $request->model_type);
        }
    }

    private function filterByModelId($query, Request $request)
    {
        if ($request->has('model_id') && $request->model_id) {
            $query->where('model_id', $request->model_id);
        }
    }
}
