<?php

declare(strict_types=1);

namespace App\Interfaces\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;

interface UserServiceInterface
{
    public function getAllUsers(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(int $id, bool $withTrashed = false): ?User;

    public function basicList(): Collection;

    public function pendingRequestsCount(int $userId): int;

    public function updateUser(User $user, array $data): User;

    public function deleteUser(User $user): void;

    public function toggleStatus(User $user): array;

    public function getPendingInvitations(): SupportCollection;
}
