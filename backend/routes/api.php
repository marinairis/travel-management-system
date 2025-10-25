<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TravelRequestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\ActivityLogController;
use App\Http\Controllers\API\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
  return response()->json([
    'success' => true,
    'message' => 'Servidor está funcionando'
  ]);
});

// Locale routes (public)
Route::get('/locales', [LocaleController::class, 'index']);
Route::get('/locale/current', [LocaleController::class, 'current']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/locations/destinations', [LocationController::class, 'getDestinations']);

Route::middleware('auth:api')->group(function () {
  Route::get('/me', [AuthController::class, 'me']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/refresh', [AuthController::class, 'refresh']);

  Route::get('/travel-requests', [TravelRequestController::class, 'index']);
  Route::post('/travel-requests', [TravelRequestController::class, 'store']);
  Route::get('/travel-requests/{id}', [TravelRequestController::class, 'show']);
  Route::put('/travel-requests/{id}', [TravelRequestController::class, 'update']);
  Route::delete('/travel-requests/{id}', [TravelRequestController::class, 'destroy']);
  Route::patch('/travel-requests/{id}/status', [TravelRequestController::class, 'updateStatus']);
  Route::patch('/travel-requests/{id}/cancel', [TravelRequestController::class, 'cancel']);

  Route::get('/users/{id}', [UserController::class, 'show']);

  Route::get('/locations/cities', [LocationController::class, 'getCities']);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
  Route::get('/users', [UserController::class, 'index']);
  Route::put('/users/{id}', [UserController::class, 'update']);
  Route::delete('/users/{id}', [UserController::class, 'destroy']);

  Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});
