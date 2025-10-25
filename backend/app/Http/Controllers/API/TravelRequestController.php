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

    public function index(TravelRequestFilterRequest $request)
    {
        $user = Auth::user();

        $query = $user->is_admin
            ? TravelRequest::with(['user', 'approvedBy'])
            : TravelRequest::where('user_id', $user->id)->with(['approvedBy']);

        $this->applyFilters($query, $request);

        $travelRequests = $query->orderBy('created_at', 'desc')->get();

        return $this->successResponse('general.success', $travelRequests);
    }

    public function store(TravelRequestFormRequest $request)
    {
        $travelRequest = TravelRequest::create([
            'user_id' => Auth::id(),
            'requester_name' => $request->requester_name,
            'destination' => $request->destination,
            'departure_date' => $request->departure_date,
            'return_date' => $request->return_date,
            'notes' => $request->notes,
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

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->is_admin) {
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

        if ($travelRequest->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível cancelar um pedido já aprovado'
            ], 403);
        }

        $oldStatus = $travelRequest->status;
        $travelRequest->status = 'cancelled';
        $travelRequest->save();

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
