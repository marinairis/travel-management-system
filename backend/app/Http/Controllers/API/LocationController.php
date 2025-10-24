<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\CityRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function __construct(private CityRepository $cityRepository) {}

    public function getCities(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q');
            $cities = $this->cityRepository->search($query);

            return response()->json([
                'success' => true,
                'data' => $cities->values(),
                'meta' => [
                    'total' => $cities->count(),
                    'has_query' => !empty($query)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar municípios',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
