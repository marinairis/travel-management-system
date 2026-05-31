<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Location;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\GetCitiesRequest;
use App\Interfaces\Repositories\CityRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetCitiesController extends Controller
{
    public function __construct(
        private readonly CityRepositoryInterface $repository
    ) {}

    public function __invoke(GetCitiesRequest $request): JsonResponse
    {
        try {
            $cities = $this->repository->search($request->get('q'));

            return response()->json([
                'success' => true,
                'data'    => $cities->values(),
                'meta'    => ['total' => $cities->count(), 'has_query' => $request->filled('q')],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar municípios: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('messages.general.server_error'),
            ], 500);
        }
    }
}
