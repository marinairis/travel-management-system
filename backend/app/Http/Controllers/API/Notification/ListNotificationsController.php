<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ListNotificationsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user          = Auth::user();
        $notifications = $user->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
            ]);

        return response()->json([
            'success'      => true,
            'data'         => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
}
