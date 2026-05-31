<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserFormRequest;
use App\Http\Requests\UserFilterRequest;
use App\Models\User;
use App\Traits\HasOwnershipValidation;
use App\Traits\HasResourceValidation;
use App\Traits\HasActivityLogging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use HasOwnershipValidation, HasResourceValidation, HasActivityLogging;

    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Listar todos os usuários (apenas admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="user_type", in="query", required=false,
     *         @OA\Schema(type="string", enum={"admin","manager","requester","basic"})),
     *     @OA\Parameter(name="email", in="query", required=false,
     *         @OA\Schema(type="string", example="maria@")),
     *     @OA\Response(response=200, description="Lista de usuários"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Acesso negado — não é admin")
     * )
     */
    public function index(UserFilterRequest $request)
    {
        $query = User::withCount('travelRequests');

        $this->applyFilters($query, $request);

        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Consultar dados de um usuário",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dados do usuário"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Usuário não encontrado")
     * )
     */
    public function show($id)
    {
        $currentUser = Auth::user();
        $user = User::withCount('travelRequests')->find($id);

        if (
            $error = $this->validateViewPermissions(
                $user,
                $currentUser,
                'Você não tem permissão para visualizar este usuário'
            )
        ) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Atualizar usuário (apenas admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Maria Silva"),
     *             @OA\Property(property="role", type="string", enum={"requester","manager","admin"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuário atualizado"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Usuário não encontrado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function update(UserFormRequest $request, $id)
    {
        $user = User::find($id);

        if (
            $error = $this->validateResourceExists(
                $user,
                'Usuário não encontrado'
            )
        ) {
            return $error;
        }

        $oldValues = $user->toArray();
        $user->update($request->only(['name', 'role']));

        $this->logActivityUpdate(
            $user,
            $oldValues,
            $request,
            'Usuário atualizado'
        );

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso',
            'data' => $user->fresh()
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Excluir usuário (apenas admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Usuário excluído"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Usuário não encontrado")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (
            $error = $this->validateResourceExists(
                $user,
                'Usuário não encontrado'
            )
        ) {
            return $error;
        }

        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode excluir sua própria conta'
            ], 403);
        }

        // Cancelar pedidos em aberto antes de excluir
        $cancelledCount = $this->cancelUserTravelRequests($user, 'Usuário excluído');

        $this->logActivityDelete($user, $request, 'Usuário excluído');

        $user->delete();

        $message = $cancelledCount > 0 
            ? "Usuário excluído com sucesso. {$cancelledCount} pedido(s) de viagem foi(ram) cancelado(s)."
            : 'Usuário excluído com sucesso.';

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function basicList()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Retorna a contagem de pedidos em aberto de um usuário
     */
    public function pendingRequestsCount($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        $count = \App\Models\TravelRequest::where('user_id', $id)
            ->whereIn('status', ['requested', 'approved'])
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count]
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/users/{id}/toggle-status",
     *     tags={"Users"},
     *     summary="Ativar ou desativar um usuário (apenas admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Status do usuário alterado com sucesso"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Usuário não encontrado")
     * )
     */
    public function toggleStatus($id)
    {
        // Busca usuário incluindo os "deletados" para poder ativar novamente
        $user = User::withTrashed()->find($id);

        if (
            $error = $this->validateResourceExists(
                $user,
                'Usuário não encontrado'
            )
        ) {
            return $error;
        }

        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode desativar sua própria conta'
            ], 403);
        }

        if ($user->is_active) {
            // DESATIVAR: cancelar pedidos primeiro, depois fazer soft delete
            $cancelledCount = $this->cancelUserTravelRequests($user, 'Usuário desativado');
            
            $user->is_active = false;
            $user->save();
            $user->delete(); // soft delete

            $this->logActivityUpdate(
                $user,
                ['is_active' => true, 'deleted_at' => null],
                new Request(['is_active' => false, 'deleted_at' => now()]),
                'Usuário desativado'
            );

            $message = $cancelledCount > 0 
                ? "Usuário desativado com sucesso. {$cancelledCount} pedido(s) de viagem foi(ram) cancelado(s)."
                : 'Usuário desativado com sucesso.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $user->fresh()
            ]);
        } else {
            // ATIVAR: restaurar do soft delete + reenviar convite
            $user->restore();
            $user->is_active = true;
            $user->save();

            // Reenviar convite por email
            $this->resendInvitation($user);

            $this->logActivityUpdate(
                $user,
                ['is_active' => false, 'deleted_at' => now()->subSecond()],
                new Request(['is_active' => true, 'deleted_at' => null]),
                'Usuário ativado'
            );

            return response()->json([
                'success' => true,
                'message' => 'Usuário ativado com sucesso. Um novo convite foi enviado por e-mail.',
                'data' => $user->fresh()
            ]);
        }
    }

    /**
     * Cancela todos os pedidos de viagem pendentes/approved de um usuário
     */
    private function cancelUserTravelRequests(User $user, string $reason)
    {
        $cancelledCount = 0;
        
        // Cancela pedidos pendentes e aprovados do usuário
        $travelRequests = \App\Models\TravelRequest::where('user_id', $user->id)
            ->whereIn('status', ['requested', 'approved'])
            ->get();
        
        foreach ($travelRequests as $request) {
            $request->status = 'cancelled';
            $request->cancel_reason = $reason;
            $request->cancelled_by = Auth::id();
            $request->cancelled_at = now();
            $request->save();
            
            // Log da atividade
            $this->logActivityUpdate(
                $request,
                ['status' => $request->getOriginal('status')],
                new Request(['status' => 'cancelled', 'cancel_reason' => $reason]),
                "Pedido cancelado - {$reason}"
            );
            
            $cancelledCount++;
        }
        
        return $cancelledCount;
    }

    /**
     * Reenvia o convite por email para o usuário
     */
    private function resendInvitation(User $user)
    {
        try {
            // Gerar novo token
            $token = \Illuminate\Support\Str::random(64);

            // Verificar se já existe convite pendente e atualizar, ou criar novo
            $invitation = \App\Models\Invitation::where('email', $user->email)
                ->whereNull('accepted_at')
                ->first();

            if ($invitation) {
                $invitation->token = $token;
                $invitation->expires_at = now()->addDays(7);
                $invitation->save();
            } else {
                \App\Models\Invitation::create([
                    'email' => $user->email,
                    'role' => $user->role,
                    'token' => $token,
                    'expires_at' => now()->addDays(7),
                ]);
            }

            // Enviar email
            $user->notify(new \App\Notifications\UserInvited($token, $user->role));
        } catch (\Exception $e) {
            // Não falhar a ativação se o email não for enviado
            \Illuminate\Support\Facades\Log::error('Erro ao reenviar convite: ' . $e->getMessage());
        }
    }

    private function applyFilters($query, UserFilterRequest $request)
    {
        $this->filterByUserType($query, $request);
        $this->filterByStatus($query, $request);
        $this->filterByEmail($query, $request);
    }

    private function filterByUserType($query, UserFilterRequest $request)
    {
        if ($request->has('user_type') && $request->user_type) {
            $type = $request->user_type;
            if ($type === 'basic') {
                $query->where('role', '!=', 'admin');
            } else {
                $query->where('role', $type);
            }
        }
    }

    private function filterByStatus($query, UserFilterRequest $request)
    {
        $status = $request->input('status');
        
        if ($status === 'active') {
            $query->where('is_active', true)->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->where(function ($q) {
                $q->where('is_active', false)->orWhereNotNull('deleted_at');
            });
        }
    }

    private function filterByEmail($query, UserFilterRequest $request)
    {
        if ($request->has('email') && $request->email) {
            $query->where('email', 'like', "%{$request->email}%");
        }
    }
}
