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
        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem visualizar logs'
            ], 403);
        }

        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

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
}
