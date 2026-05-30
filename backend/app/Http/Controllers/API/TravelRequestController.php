<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequestFormRequest;
use App\Http\Requests\TravelRequestStatusRequest;
use App\Http\Requests\TravelRequestFilterRequest;
use App\Models\TravelRequest;
use App\Notifications\TravelRequestStatusChanged;
use App\Traits\HasOwnershipValidation;
use App\Traits\HasResourceValidation;
use App\Traits\HasActivityLogging;
use App\Traits\HasTranslations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelRequestController extends Controller
{
    use HasOwnershipValidation, HasResourceValidation, HasActivityLogging, HasTranslations;

    /**
     * @OA\Get(
     *     path="/api/travel-requests",
     *     tags={"Travel Requests"},
     *     summary="Listar pedidos de viagem",
     *     description="Admin/Manager veem todos os pedidos. Solicitante vê apenas os próprios.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="status", in="query", required=false,
     *         @OA\Schema(type="string", enum={"requested","approved","cancelled"})),
     *     @OA\Parameter(name="destination", in="query", required=false,
     *         @OA\Schema(type="string", example="São Paulo")),
     *     @OA\Parameter(name="start_date", in="query", required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")),
     *     @OA\Parameter(name="end_date", in="query", required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")),
     *     @OA\Response(response=200, description="Lista de pedidos"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function index(TravelRequestFilterRequest $request)
    {
        $user = Auth::user();

        $query = $user->isApprover()
            ? TravelRequest::with(['user', 'approvedBy'])
            : TravelRequest::where('user_id', $user->id)->with(['user', 'approvedBy']);

        $this->applyFilters($query, $request);

        $perPage = $request->input('per_page', 10);
        $travelRequests = $query->orderBy('created_at', 'desc')->paginate($perPage);

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
     * @OA\Post(
     *     path="/api/travel-requests",
     *     tags={"Travel Requests"},
     *     summary="Criar novo pedido de viagem",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"requester_name","destination","departure_date","return_date"},
     *             @OA\Property(property="requester_name", type="string", example="Maria Silva"),
     *             @OA\Property(property="destination", type="string", example="São Paulo, SP"),
     *             @OA\Property(property="departure_date", type="string", format="date", example="2025-08-01"),
     *             @OA\Property(property="return_date", type="string", format="date", example="2025-08-05"),
     *             @OA\Property(property="notes", type="string", example="Reunião com cliente")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Pedido criado com sucesso"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function store(TravelRequestFormRequest $request)
    {
        $travelRequest = TravelRequest::create([
            'user_id' => Auth::id(),
            'requester_name' => $request->requester_name,
            'destination' => $request->destination,
            'departure_date' => $request->departure_date,
            'return_date' => $request->return_date,
            'notes' => $request->notes,
            'travel_type' => $request->travel_type,
            'status' => 'requested',
        ]);

        $this->logActivityCreate(
            $travelRequest,
            $request,
            'Pedido de viagem criado'
        );

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem criado com sucesso',
            'data' => $travelRequest->load(['user', 'approvedBy'])
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/travel-requests/{id}",
     *     tags={"Travel Requests"},
     *     summary="Consultar pedido de viagem por ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dados do pedido"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Pedido não encontrado")
     * )
     */
    public function show($id)
    {
        $user = Auth::user();
        $travelRequest = TravelRequest::with(['user', 'approvedBy'])->find($id);

        if (
            $error = $this->validateViewPermissions(
                $travelRequest,
                $user,
                'Você não tem permissão para visualizar este pedido'
            )
        ) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'data' => $travelRequest
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/travel-requests/{id}",
     *     tags={"Travel Requests"},
     *     summary="Atualizar dados de um pedido de viagem",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="requester_name", type="string"),
     *             @OA\Property(property="destination", type="string"),
     *             @OA\Property(property="departure_date", type="string", format="date"),
     *             @OA\Property(property="return_date", type="string", format="date"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Pedido atualizado"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Sem permissão ou pedido já aprovado"),
     *     @OA\Response(response=404, description="Pedido não encontrado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function update(TravelRequestFormRequest $request, $id)
    {
        $user = Auth::user();
        $travelRequest = TravelRequest::find($id);

        if (
            $error = $this->validateUpdatePermissions(
                $travelRequest,
                $user,
                'Você não tem permissão para atualizar este pedido',
                'Não é possível atualizar um pedido já aprovado'
            )
        ) {
            return $error;
        }

        $oldValues = $travelRequest->toArray();
        $travelRequest->update($request->all());

        $this->logActivityUpdate(
            $travelRequest,
            $oldValues,
            $request,
            'Pedido de viagem atualizado'
        );

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem atualizado com sucesso',
            'data' => $travelRequest->fresh()->load(['user', 'approvedBy'])
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/travel-requests/{id}/status",
     *     tags={"Travel Requests"},
     *     summary="Atualizar status de um pedido (aprovado/cancelado)",
     *     description="Apenas Gestor ou Admin. O usuário que criou o pedido não pode alterar seu próprio status.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"status"},
     *             @OA\Property(property="status", type="string", enum={"approved","cancelled"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status atualizado com sucesso"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Pedido não encontrado"),
     *     @OA\Response(response=422, description="Status inválido")
     * )
     */
    public function updateStatus(TravelRequestStatusRequest $request, $id)
    {
        $user = Auth::user();
        $travelRequest = TravelRequest::find($id);

        if (
            $error = $this->validateResourceExists(
                $travelRequest,
                'Pedido de viagem não encontrado'
            )
        ) {
            return $error;
        }

        if ($travelRequest->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode alterar o status do seu próprio pedido'
            ], 403);
        }

        $oldStatus = $travelRequest->status;
        $travelRequest->status = $request->status;
        $travelRequest->approved_by = $user->id;
        $travelRequest->approved_at = now();
        $travelRequest->save();

        $travelRequest->user->notify(
            new TravelRequestStatusChanged($travelRequest, $oldStatus)
        );

        $this->logActivityStatusChange(
            $travelRequest,
            $oldStatus,
            $request->status,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'data' => $travelRequest->fresh()->load(['user', 'approvedBy'])
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/travel-requests/{id}",
     *     tags={"Travel Requests"},
     *     summary="Excluir pedido de viagem (Gestor ou Admin)",
     *     description="Pedidos aprovados não podem ser excluídos.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Pedido excluído"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Sem permissão ou pedido aprovado"),
     *     @OA\Response(response=404, description="Pedido não encontrado")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem excluir pedidos'
            ], 403);
        }

        $travelRequest = TravelRequest::find($id);

        if (
            $error = $this->validateResourceExists(
                $travelRequest,
                'Pedido de viagem não encontrado'
            )
        ) {
            return $error;
        }

        if ($travelRequest->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir um pedido aprovado'
            ], 403);
        }

        $this->logActivityDelete(
            $travelRequest,
            $request,
            'Pedido de viagem excluído'
        );

        $travelRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem excluído com sucesso'
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/travel-requests/{id}/cancel",
     *     tags={"Travel Requests"},
     *     summary="Cancelar pedido de viagem",
     *     description="Pedidos aprovados só podem ser cancelados se a data de partida ainda não passou.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Pedido cancelado com sucesso"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Pedido não encontrado"),
     *     @OA\Response(response=422, description="Pedido já cancelado ou data de partida passou")
     * )
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        $travelRequest = TravelRequest::find($id);

        if (
            $error = $this->validateViewPermissions(
                $travelRequest,
                $user,
                'Você não tem permissão para cancelar este pedido'
            )
        ) {
            return $error;
        }

        if ($travelRequest->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Este pedido já está cancelado'
            ], 422);
        }

        if ($travelRequest->departure_date < now()->startOfDay()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível cancelar um pedido cuja data de partida já passou'
            ], 422);
        }

        $oldStatus = $travelRequest->status;
        $travelRequest->status = 'cancelled';
        $travelRequest->save();

        $travelRequest->user->notify(
            new TravelRequestStatusChanged($travelRequest, $oldStatus)
        );

        $this->logActivityStatusChange(
            $travelRequest,
            $oldStatus,
            'cancelled',
            $request,
            'Pedido de viagem cancelado'
        );

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado com sucesso',
            'data' => $travelRequest->fresh()->load(['user', 'approvedBy'])
        ]);
    }

    private function applyFilters($query, TravelRequestFilterRequest $request)
    {
        $this->filterByStatus($query, $request);
        $this->filterByDestination($query, $request);
        $this->filterByDateRange($query, $request);
    }

    private function filterByStatus($query, TravelRequestFilterRequest $request)
    {
        if ($request->has('status') && $request->status) {
            $query->byStatus($request->status);
        }
    }

    private function filterByDestination($query, TravelRequestFilterRequest $request)
    {
        if ($request->has('destination') && $request->destination) {
            $query->byDestination($request->destination);
        }
    }

    private function filterByDateRange($query, TravelRequestFilterRequest $request)
    {
        if (
            $request->has('start_date') && $request->has('end_date') &&
            $request->start_date && $request->end_date
        ) {
            $query->byDateRange($request->start_date, $request->end_date);
        }
    }
}
