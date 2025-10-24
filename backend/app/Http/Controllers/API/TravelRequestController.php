<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TravelRequest;
use App\Models\ActivityLog;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TravelRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->is_admin
            ? TravelRequest::with(['user', 'approvedBy'])
            : TravelRequest::where('user_id', $user->id)->with(['approvedBy']);

        // Filtros
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('destination')) {
            $query->byDestination($request->destination);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        $travelRequests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $travelRequests
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requester_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after:departure_date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $travelRequest = TravelRequest::create([
            'user_id' => auth()->id(),
            'requester_name' => $request->requester_name,
            'destination' => $request->destination,
            'departure_date' => $request->departure_date,
            'return_date' => $request->return_date,
            'notes' => $request->notes,
            'status' => 'requested',
        ]);

        // Log da atividade
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => TravelRequest::class,
            'model_id' => $travelRequest->id,
            'description' => 'Pedido de viagem criado',
            'new_values' => $travelRequest->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem criado com sucesso',
            'data' => $travelRequest->load(['user', 'approvedBy'])
        ], 201);
    }

    public function show($id)
    {
        $user = auth()->user();

        $travelRequest = TravelRequest::with(['user', 'approvedBy'])->find($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        // Verifica se o usuário tem permissão para ver este pedido
        if (!$user->is_admin && $travelRequest->user_id !== $user->id) {
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

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $travelRequest = TravelRequest::find($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        // Verifica se o usuário tem permissão para atualizar
        if (!$user->is_admin && $travelRequest->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para atualizar este pedido'
            ], 403);
        }

        // Usuário não admin só pode atualizar seus próprios pedidos que não foram aprovados
        if (!$user->is_admin && $travelRequest->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível atualizar um pedido já aprovado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'requester_name' => 'sometimes|string|max:255',
            'destination' => 'sometimes|string|max:255',
            'departure_date' => 'sometimes|date|after_or_equal:today',
            'return_date' => 'sometimes|date|after:departure_date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldValues = $travelRequest->toArray();
        $travelRequest->update($request->all());

        // Log da atividade
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => TravelRequest::class,
            'model_id' => $travelRequest->id,
            'description' => 'Pedido de viagem atualizado',
            'old_values' => $oldValues,
            'new_values' => $travelRequest->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem atualizado com sucesso',
            'data' => $travelRequest->fresh()->load(['user', 'approvedBy'])
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem alterar o status'
            ], 403);
        }

        $travelRequest = TravelRequest::find($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        if ($travelRequest->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode alterar o status do seu próprio pedido'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $travelRequest->status;
        $travelRequest->status = $request->status;
        $travelRequest->approved_by = $user->id;
        $travelRequest->approved_at = now();
        $travelRequest->save();

        $travelRequest->user->notify(new TravelRequestStatusChanged($travelRequest, $oldStatus));

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'status_change',
            'model_type' => TravelRequest::class,
            'model_id' => $travelRequest->id,
            'description' => "Status alterado de {$oldStatus} para {$request->status}",
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $request->status],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'data' => $travelRequest->fresh()->load(['user', 'approvedBy'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem excluir pedidos'
            ], 403);
        }

        $travelRequest = TravelRequest::find($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        if ($travelRequest->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir um pedido aprovado'
            ], 403);
        }

        // Log da atividade
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'delete',
            'model_type' => TravelRequest::class,
            'model_id' => $travelRequest->id,
            'description' => 'Pedido de viagem excluído',
            'old_values' => $travelRequest->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $travelRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pedido de viagem excluído com sucesso'
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $user = auth()->user();
        $travelRequest = TravelRequest::find($id);

        if (!$travelRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido de viagem não encontrado'
            ], 404);
        }

        // Verifica permissão
        if (!$user->is_admin && $travelRequest->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para cancelar este pedido'
            ], 403);
        }

        // Não pode cancelar se já foi aprovado
        if ($travelRequest->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível cancelar um pedido já aprovado'
            ], 403);
        }

        $oldStatus = $travelRequest->status;
        $travelRequest->status = 'cancelled';
        $travelRequest->save();

        // Log da atividade
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'cancel',
            'model_type' => TravelRequest::class,
            'model_id' => $travelRequest->id,
            'description' => 'Pedido de viagem cancelado',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'cancelled'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado com sucesso',
            'data' => $travelRequest->fresh()->load(['user', 'approvedBy'])
        ]);
    }
}
