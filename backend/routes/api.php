<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TravelRequestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\ActivityLogController;
use App\Http\Controllers\API\InvitationController;
use App\Http\Controllers\API\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Servidor está funcionando'
    ]);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/locations/destinations', [LocationController::class, 'getDestinations']);
Route::get('/invitations/{token}', [InvitationController::class, 'show']);
Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::get('/travel-requests', [TravelRequestController::class, 'index']);
    Route::post('/travel-requests', [TravelRequestController::class, 'store']);
    Route::get('/travel-requests/{id}', [TravelRequestController::class, 'show']);
    Route::put('/travel-requests/{id}', [TravelRequestController::class, 'update']);
    Route::patch('/travel-requests/{id}/cancel', [TravelRequestController::class, 'cancel']);

    Route::get('/users/basic', [UserController::class, 'basicList']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::get('/locations/cities', [LocationController::class, 'getCities']);

    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

Route::middleware(['auth:api', 'manager_or_admin'])->group(function () {
    Route::delete('/travel-requests/{id}', [TravelRequestController::class, 'destroy']);
    Route::patch('/travel-requests/{id}/status', [TravelRequestController::class, 'updateStatus']);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/invite', [InvitationController::class, 'invite']);
    Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::get('/users/{id}/pending-requests-count', [UserController::class, 'pendingRequestsCount']);
});
