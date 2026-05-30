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

        $users = $query->get();

        return response()->json([
            'success' => true,
            'data' => $users
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

        $this->logActivityDelete($user, $request, 'Usuário excluído');

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuário excluído com sucesso'
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

    private function applyFilters($query, UserFilterRequest $request)
    {
        $this->filterByUserType($query, $request);
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

    private function filterByEmail($query, UserFilterRequest $request)
    {
        if ($request->has('email') && $request->email) {
            $query->where('email', 'like', "%{$request->email}%");
        }
    }
}
