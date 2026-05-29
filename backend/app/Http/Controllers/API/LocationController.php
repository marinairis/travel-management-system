<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\CityRepository;
use App\Traits\HasActivityLogging;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    use HasActivityLogging;

    public function __construct(private CityRepository $cityRepository) {}

    /**
     * @OA\Get(
     *     path="/api/locations/cities",
     *     tags={"Locations"},
     *     summary="Buscar municípios brasileiros via IBGE",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="q", in="query", required=false,
     *         description="Texto para busca por nome do município",
     *         @OA\Schema(type="string", example="São Paulo")),
     *     @OA\Response(response=200, description="Lista de municípios filtrada"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=500, description="Erro ao consultar API do IBGE")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/locations/destinations",
     *     tags={"Locations"},
     *     summary="Listar destinos já utilizados em pedidos (público)",
     *     @OA\Response(response=200, description="Lista de destinos em cache"),
     *     @OA\Response(response=500, description="Erro ao buscar destinos")
     * )
     */
    public function getDestinations(): JsonResponse
    {
        try {
            $destinations = $this->cityRepository->getDestinations();

            return response()->json([
                'success' => true,
                'data' => $destinations->values(),
                'meta' => [
                    'total' => $destinations->count(),
                    'cached' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar destinos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
