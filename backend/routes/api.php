<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TravelRequestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LocationController;
use App\Http\Controllers\API\ActivityLogController;
use Illuminate\Support\Facades\Route;

// crie uma rota para testar se o servidor está funcionando
Route::get('/test', function () {
  return response()->json([
    'success' => true,
    'message' => 'Servidor está funcionando'
  ]);
});

// Rotas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/me', [AuthController::class, 'me']);
  Route::post('/logout', [AuthController::class, 'logout']);
  // Travel Requests
  Route::get('/travel-requests', [TravelRequestController::class, 'index']);
  Route::post('/travel-requests', [TravelRequestController::class, 'store']);
  Route::get('/travel-requests/{id}', [TravelRequestController::class, 'show']);
  Route::put('/travel-requests/{id}', [TravelRequestController::class, 'update']);
  Route::delete('/travel-requests/{id}', [TravelRequestController::class, 'destroy']);
  Route::patch('/travel-requests/{id}/status', [TravelRequestController::class, 'updateStatus']);
  Route::patch('/travel-requests/{id}/cancel', [TravelRequestController::class, 'cancel']);

  // Users
  Route::get('/users', [UserController::class, 'index']);
  Route::get('/users/{id}', [UserController::class, 'show']);
  Route::put('/users/{id}', [UserController::class, 'update']);
  Route::delete('/users/{id}', [UserController::class, 'destroy']);

  // Locations
  Route::get('/locations/cities', [LocationController::class, 'getCities']);

  // Activity Logs
  Route::get('/activity-logs', [ActivityLogController::class, 'index']);
});
