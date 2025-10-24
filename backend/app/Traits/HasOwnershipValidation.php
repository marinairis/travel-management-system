<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

trait HasOwnershipValidation
{
  protected function canAccessResource($resource, $user = null): bool
  {
    $user = $user ?? Auth::user();

    return $user->is_admin || $resource->user_id === $user->id;
  }

  protected function canUpdateResource($resource, $user = null): bool
  {
    $user = $user ?? Auth::user();

    if ($user->is_admin) {
      return true;
    }

    return $resource->user_id === $user->id;
  }

  protected function canModifyResource($resource): bool
  {
    return $resource->status !== 'approved';
  }

  protected function permissionDeniedResponse(string $message = 'Você não tem permissão para acessar este recurso'): JsonResponse
  {
    return response()->json([
      'success' => false,
      'message' => $message
    ], 403);
  }

  protected function resourceNotFoundResponse(string $message = 'Recurso não encontrado'): JsonResponse
  {
    return response()->json([
      'success' => false,
      'message' => $message
    ], 404);
  }

  protected function resourceNotModifiableResponse(string $message = 'Este recurso não pode ser modificado'): JsonResponse
  {
    return response()->json([
      'success' => false,
      'message' => $message
    ], 403);
  }
}
