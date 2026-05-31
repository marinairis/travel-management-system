<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Models\User;

interface DashboardRepositoryInterface
{
    public function getStats(User $user): array;
}
