<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasActivityLogging
{
    protected function logActivityCreate(Model $model, ?string $description = null): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => $description ?? ucfirst(class_basename($model)).' criado',
            'new_values' => $model->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function logActivityUpdate(Model $model, array $oldValues, ?string $description = null): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => $description ?? ucfirst(class_basename($model)).' atualizado',
            'old_values' => $oldValues,
            'new_values' => $model->fresh()->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function logActivityDelete(Model $model, ?string $description = null): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => $description ?? ucfirst(class_basename($model)).' excluído',
            'old_values' => $model->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function logActivityStatusChange(Model $model, string $oldStatus, string $newStatus): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'status_change',
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => "Status alterado de {$oldStatus} para {$newStatus}",
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
