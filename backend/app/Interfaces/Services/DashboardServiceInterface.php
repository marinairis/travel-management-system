<?php

declare(strict_types=1);

namespace App\Interfaces\Services;

use App\Models\User;

interface DashboardServiceInterface
{
    public function getStats(User $user): array;
}
