<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Repositories\DashboardRepositoryInterface;
use App\Interfaces\Services\DashboardServiceInterface;
use App\Models\User;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repository
    ) {}

    public function getStats(User $user): array
    {
        return $this->repository->getStats($user);
    }

    public function getOldestPendingApproval(User $user): array
    {
        return $this->repository->getOldestPendingApproval($user);
    }

    public function getRecentRequests(User $user): array
    {
        return $this->repository->getRecentRequests($user);
    }
}
