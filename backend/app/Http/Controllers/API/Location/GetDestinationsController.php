<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Location;

use App\Http\Controllers\Controller;
use App\Interfaces\Repositories\CityRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GetDestinationsController extends Controller
{
    public function __construct(
        private readonly CityRepositoryInterface $repository
    ) {}

    public function __invoke(): JsonResponse
    {
        try {
            $destinations = $this->repository->getDestinations();

            return response()->json([
                'success' => true,
                'data'    => $destinations->values(),
                'meta'    => ['total' => $destinations->count(), 'cached' => true],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar destinos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('messages.general.server_error'),
            ], 500);
        }
    }
}
