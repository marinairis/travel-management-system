<?php

use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\MeController;
use App\Http\Controllers\API\Auth\RefreshTokenController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\API\TravelRequest\CancelTravelRequestController;
use App\Http\Controllers\API\TravelRequest\CreateTravelRequestController;
use App\Http\Controllers\API\TravelRequest\ListTravelRequestsController;
use App\Http\Controllers\API\TravelRequest\ShowTravelRequestController;
use App\Http\Controllers\API\TravelRequest\UpdateTravelRequestController;
use App\Http\Controllers\API\TravelRequest\UpdateTravelRequestStatusController;
use App\Http\Controllers\API\User\BasicListUsersController;
use App\Http\Controllers\API\User\DeleteUserController;
use App\Http\Controllers\API\User\ListUsersController;
use App\Http\Controllers\API\User\PendingRequestsCountController;
use App\Http\Controllers\API\User\ShowUserController;
use App\Http\Controllers\API\User\ToggleUserStatusController;
use App\Http\Controllers\API\User\UpdateUserController;
use App\Http\Controllers\API\Invitation\AcceptInvitationController;
use App\Http\Controllers\API\Invitation\InviteUserController;
use App\Http\Controllers\API\Invitation\ShowInvitationController;
use App\Http\Controllers\API\ActivityLog\ListActivityLogsController;
use App\Http\Controllers\API\Dashboard\GetDashboardStatsController;
use App\Http\Controllers\API\Dashboard\GetPendingApprovalController;
use App\Http\Controllers\API\Dashboard\GetRecentRequestsController;
use App\Http\Controllers\API\Notification\ListNotificationsController;
use App\Http\Controllers\API\Notification\MarkAllNotificationsAsReadController;
use App\Http\Controllers\API\Notification\MarkNotificationAsReadController;
use App\Http\Controllers\API\Location\GetCitiesController;
use App\Http\Controllers\API\Location\GetDestinationsController;
use Illuminate\Support\Facades\Route;

Route::get('/test', fn () => response()->json(['success' => true, 'message' => 'Ok']));

// Rotas públicas
Route::post('/login', LoginController::class);
Route::post('/forgot-password', ForgotPasswordController::class);
Route::post('/reset-password', ResetPasswordController::class);
Route::get('/locations/destinations', GetDestinationsController::class);
Route::get('/invitations/{token}', ShowInvitationController::class);
Route::post('/invitations/{token}/accept', AcceptInvitationController::class);

// Rotas autenticadas
Route::middleware('auth:api')->group(function () {
    Route::get('/me', MeController::class);
    Route::post('/logout', LogoutController::class);
    Route::post('/refresh', RefreshTokenController::class);

    Route::get('/dashboard/stats', GetDashboardStatsController::class);
    Route::get('/dashboard/pending-approval', GetPendingApprovalController::class);
    Route::get('/dashboard/recent-requests', GetRecentRequestsController::class);

    Route::get('/travel-requests', ListTravelRequestsController::class);
    Route::post('/travel-requests', CreateTravelRequestController::class);
    Route::get('/travel-requests/{id}', ShowTravelRequestController::class);
    Route::put('/travel-requests/{id}', UpdateTravelRequestController::class);
    Route::patch('/travel-requests/{id}/cancel', CancelTravelRequestController::class);

    Route::get('/users/basic', BasicListUsersController::class);
    Route::get('/users/{id}', ShowUserController::class);
    Route::get('/locations/cities', GetCitiesController::class);

    Route::get('/activity-logs', ListActivityLogsController::class);

    Route::get('/notifications', ListNotificationsController::class);
    Route::patch('/notifications/read-all', MarkAllNotificationsAsReadController::class);
    Route::patch('/notifications/{id}/read', MarkNotificationAsReadController::class);
});

// Rotas para manager ou admin
Route::middleware(['auth:api', 'manager_or_admin'])->group(function () {
    Route::patch('/travel-requests/{id}/status', UpdateTravelRequestStatusController::class);
});

// Rotas exclusivas de admin
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/users', ListUsersController::class);
    Route::put('/users/{id}', UpdateUserController::class);
    Route::delete('/users/{id}', DeleteUserController::class);
    Route::post('/users/invite', InviteUserController::class);
    Route::patch('/users/{id}/toggle-status', ToggleUserStatusController::class);
    Route::get('/users/{id}/pending-requests-count', PendingRequestsCountController::class);
});
