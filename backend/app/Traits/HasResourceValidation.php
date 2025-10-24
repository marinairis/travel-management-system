<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

trait HasResourceValidation
{
  use HasOwnershipValidation;

  protected function validateResourceExists(Model $resource, string $message = 'Recurso não encontrado'): ?JsonResponse
  {
    if (!$resource) {
      return $this->resourceNotFoundResponse($message);
    }

    return null;
  }

  protected function validateResourceAccess(Model $resource, $user = null, string $message = 'Você não tem permissão para acessar este recurso'): ?JsonResponse
  {
    if (!$this->canAccessResource($resource, $user)) {
      return $this->permissionDeniedResponse($message);
    }

    return null;
  }

  protected function validateResourceUpdate(Model $resource, $user = null, string $message = 'Você não tem permissão para atualizar este recurso'): ?JsonResponse
  {
    if (!$this->canUpdateResource($resource, $user)) {
      return $this->permissionDeniedResponse($message);
    }

    return null;
  }

  protected function validateResourceModification(Model $resource, string $message = 'Este recurso não pode ser modificado'): ?JsonResponse
  {
    if (!$this->canModifyResource($resource)) {
      return $this->resourceNotModifiableResponse($message);
    }

    return null;
  }

  protected function validateUpdatePermissions(Model $resource, $user = null): ?JsonResponse
  {
    if ($error = $this->validateResourceExists($resource)) {
      return $error;
    }

    if ($error = $this->validateResourceUpdate($resource, $user)) {
      return $error;
    }

    if ($error = $this->validateResourceModification($resource)) {
      return $error;
    }

    return null;
  }

  protected function validateViewPermissions(Model $resource, $user = null): ?JsonResponse
  {
    if ($error = $this->validateResourceExists($resource)) {
      return $error;
    }

    if ($error = $this->validateResourceAccess($resource, $user)) {
      return $error;
    }

    return null;
  }
}
