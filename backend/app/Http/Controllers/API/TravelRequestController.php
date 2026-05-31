<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequestFormRequest;
use App\Http\Requests\TravelRequestStatusRequest;
use App\Http\Requests\TravelRequestFilterRequest;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * TravelRequestController
 * 
 * Handles all travel request operations including CRUD, status updates,
 * and cancellations. Implements role-based access control where:
 * - Requesters can only view/edit their own requests
 * - Managers/Admins can view all requests and update status
 * 
 * @group Travel Requests
 */
class TravelRequestController extends Controller
{
    private TravelRequestService $travelRequestService;

    public function __construct(TravelRequestService $travelRequestService)
    {
        $this->travelRequestService = $travelRequestService;
    }

    /**
     * List all travel requests
     * 
     * Admin/Manager see all requests. Requesters see only their own.
     * Supports filtering by status, destination, and date range.
     * 
     * @param TravelRequestFilterRequest $request
     * @return JsonResponse
     * 
     * @queryParam status string Filter by status (requested, approved, cancelled, expired). Example: approved
     * @queryParam destination string Filter by destination (partial match). Example: São Paulo
     * @queryParam start_date date Filter by date range start (YYYY-MM-DD). Example: 2025-01-01
     * @queryParam end_date date Filter by date range end (YYYY-MM-DD). Example: 2025-12-31
     * @queryParam per_page int Number of items per page (default: 10). Example: 10
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 5,
     *     "per_page": 10,
     *     "total": 50
     *   }
     * }
     */
    public function index(TravelRequestFilterRequest $request): JsonResponse
    {
        $travelRequests = $this->travelRequestService->getAllTravelRequests($request);

        return response()->json([
            'success' => true,
            'data' => $travelRequests->items(),
            'meta' => [
                'current_page' => $travelRequests->currentPage(),
                'last_page' => $travelRequests->lastPage(),
                'per_page' => $travelRequests->perPage(),
                'total' => $travelRequests->total(),
            ]
        ]);
    }

    /**
     * Create a new travel request
     * 
     * Creates a new travel request associated with the authenticated user.
     * The request starts with status "requested".
     * 
     * @param TravelRequestFormRequest $request
     * @return JsonResponse
     * 
     * @bodyParam requester_name string required The name of the requester. Example: Maria Silva
     * @bodyParam destination string required The travel destination. Example: São Paulo, SP
     * @bodyParam departure_date date required Departure date (YYYY-MM-DD). Example: 2025-08-01
     * @bodyParam return_date date required Return date (YYYY-MM-DD). Example: 2025-08-05
     * @bodyParam notes string optional Additional notes about the trip. Example: Reunião com cliente
     * @bodyParam travel_type string optional Type of travel. Example: business
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Pedido de viagem criado com sucesso",
     *   "data": {...}
     * }
     * @response 422 Validation error
     */
    public function store(TravelRequestFormRequest $request): JsonResponse
    {
        $travelRequest = $this->travelRequestService->createTravelRequest($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem criado com sucesso',
            'data' => $travelRequest
        ], 201);
    }

    /**
     * Show a specific travel request
     * 
     * Returns detailed information about a travel request.
     * Requesters can only view their own requests.
     * 
     * @param int $id Travel request ID
     * @return JsonResponse
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {...}
     * }
     * @response 403 Access denied
     * @response 404 Travel request not found
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $travelRequest = $this->travelRequestService->getTravelRequest($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        if (!$this->travelRequestService->canViewTravelRequest($travelRequest, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar este pedido'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $travelRequest
        ]);
    }

    /**
     * Update travel request data
     * 
     * Updates the travel request details. Only the owner can update
     * (unless admin). Cannot update already approved requests.
     * 
     * @param TravelRequestFormRequest $request
     * @param int $id Travel request ID
     * @return JsonResponse
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Pedido de viagem atualizado com sucesso",
     *   "data": {...}
     * }
     * @response 403 Access denied or request already approved
     * @response 404 Travel request not found
     * @response 422 Validation error
     */
    public function update(TravelRequestFormRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $travelRequest = $this->travelRequestService->getTravelRequest($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        if (!$this->travelRequestService->canUpdateTravelRequest($travelRequest, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para atualizar este pedido'
            ], 403);
        }

        if (!$this->travelRequestService->canModifyTravelRequest($travelRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível atualizar um pedido já aprovado'
            ], 403);
        }

        $travelRequest = $this->travelRequestService->updateTravelRequest(
            $travelRequest,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem atualizado com sucesso',
            'data' => $travelRequest
        ]);
    }

    /**
     * Update travel request status
     * 
     * Allows managers and admins to approve or reject requests.
     * Users cannot change the status of their own requests.
     * Sends notification to the requester upon status change.
     * 
     * @param TravelRequestStatusRequest $request
     * @param int $id Travel request ID
     * @return JsonResponse
     * 
     * @bodyParam status string required New status (approved, cancelled). Example: approved
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Status atualizado com sucesso",
     *   "data": {...}
     * }
     * @response 403 Cannot change own request status
     * @response 404 Travel request not found
     * @response 422 Invalid status
     */
    public function updateStatus(TravelRequestStatusRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $travelRequest = $this->travelRequestService->getTravelRequest($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        if (!$this->travelRequestService->canUpdateStatus($travelRequest, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode alterar o status do seu próprio pedido'
            ], 403);
        }

        $travelRequest = $this->travelRequestService->updateStatus(
            $travelRequest,
            $request->status,
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'data' => $travelRequest
        ]);
    }

    /**
     * Cancel a travel request
     * 
     * Cancels a travel request. Approved requests can only be cancelled
     * if the departure date has not passed. Sends notification to the requester.
     * 
     * @param Request $request
     * @param int $id Travel request ID
     * @return JsonResponse
     * 
     * @bodyParam reason string optional Reason for cancellation. Example: Mudança de planos
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Pedido cancelado com sucesso",
     *   "data": {...}
     * }
     * @response 403 Cannot cancel (permission denied, already cancelled, or past departure)
     * @response 404 Travel request not found
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $travelRequest = $this->travelRequestService->getTravelRequest($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        // Check if requester is trying to cancel themselves
        if ($travelRequest->user_id === $user->id && !$user->isApprover()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para cancelar este pedido'
            ], 403);
        }

        if ($travelRequest->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Este pedido já está cancelado'
            ], 422);
        }

        if ($travelRequest->status === 'expired') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível cancelar um pedido vencido'
            ], 422);
        }

        if (!$this->travelRequestService->canBeCancelled($travelRequest)) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível cancelar um pedido aprovado cuja data de partida já passou'
            ], 422);
        }

        $travelRequest = $this->travelRequestService->cancelRequest(
            $travelRequest,
            $user,
            $request->input('reason', '')
        );

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado com sucesso',
            'data' => $travelRequest
        ]);
    }

}
