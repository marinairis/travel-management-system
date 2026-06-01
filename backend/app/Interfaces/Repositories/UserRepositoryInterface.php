<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id, bool $withTrashed = false): ?User;

    public function findAllPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function basicList(): Collection;

    public function cancelOpenRequests(User $user, string $reason): int;
}
