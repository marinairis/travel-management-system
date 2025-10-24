<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        if (!auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem listar usuários'
            ], 403);
        }

        $users = User::withCount('travelRequests')->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function show($id)
    {
        $user = User::withCount('travelRequests')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        // Verifica permissão
        if (!auth()->user()->is_admin && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar este usuário'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem atualizar usuários'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'is_admin' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldValues = $user->toArray();
        $user->update($request->only(['name', 'is_admin']));

        // Log da atividade
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => 'Usuário atualizado',
            'old_values' => $oldValues,
            'new_values' => $user->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso',
            'data' => $user->fresh()
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem excluir usuários'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode excluir sua própria conta'
            ], 403);
        }

        // Log da atividade
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => 'Usuário excluído',
            'old_values' => $user->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuário excluído com sucesso'
        ]);
    }
}
