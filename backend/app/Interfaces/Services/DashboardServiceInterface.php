<?php

declare(strict_types=1);

namespace App\Interfaces\Services;

use App\Models\User;

interface DashboardServiceInterface
{
    public function getStats(User $user): array;

    public function getOldestPendingApproval(User $user): array;

    public function getRecentRequests(User $user): array;
}
