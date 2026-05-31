<?php

declare(strict_types=1);

namespace App\Interfaces\Repositories;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface TravelRequestRepositoryInterface
{
    public function findById(int $id): ?TravelRequest;

    public function findAllPaginated(User $user, array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): TravelRequest;

    public function save(TravelRequest $travelRequest): TravelRequest;
}
