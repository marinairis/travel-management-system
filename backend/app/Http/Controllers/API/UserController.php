<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserFormRequest;
use App\Http\Requests\UserFilterRequest;
use App\Models\User;
use App\Models\ActivityLog;
use App\Traits\HasOwnershipValidation;
use App\Traits\HasResourceValidation;
use App\Traits\HasActivityLogging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use HasOwnershipValidation, HasResourceValidation, HasActivityLogging;

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
        $user->update($request->only(['name', 'is_admin']));

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

    private function applyFilters($query, UserFilterRequest $request)
    {
        $this->filterByUserType($query, $request);
        $this->filterByEmail($query, $request);
    }

    private function filterByUserType($query, UserFilterRequest $request)
    {
        if ($request->has('user_type') && $request->user_type) {
            if ($request->user_type === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->user_type === 'basic') {
                $query->where('is_admin', false);
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
