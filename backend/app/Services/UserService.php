<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\User\UserException;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Services\UserServiceInterface;
use App\Models\Invitation;
use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\UserInvited;
use App\Traits\HasActivityLogging;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserService implements UserServiceInterface
{
    use HasActivityLogging;

    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function getAllUsers(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->findAllPaginated($filters, $perPage);
    }

    public function findById(int $id, bool $withTrashed = false): ?User
    {
        return $this->repository->findById($id, $withTrashed);
    }

    public function basicList(): Collection
    {
        return $this->repository->basicList();
    }

    public function pendingRequestsCount(int $userId): int
    {
        return TravelRequest::where('user_id', $userId)
            ->whereIn('status', ['requested', 'approved'])
            ->count();
    }

    public function updateUser(User $user, array $data): User
    {
        $oldValues = $user->toArray();
        $user->update($data);
        $this->logActivityUpdate($user, $oldValues);
        return $user->fresh();
    }

    public function deleteUser(User $user): void
    {
        $this->repository->cancelOpenRequests($user, __('messages.user.deleted'));
        $this->logActivityDelete($user);
        $user->delete();
    }

    public function toggleStatus(User $user): array
    {
        if ($user->is_active) {
            return $this->deactivateUser($user);
        }

        return $this->activateUser($user);
    }

    private function deactivateUser(User $user): array
    {
        $cancelledCount = $this->repository->cancelOpenRequests($user, __('messages.user.deactivated'));

        $oldValues = ['is_active' => true, 'deleted_at' => null];

        $user->is_active = false;
        $user->save();
        $user->delete();

        $this->logActivityUpdate($user, $oldValues);

        return [
            'user'            => $user->fresh(),
            'cancelled_count' => $cancelledCount,
            'action'          => 'deactivated',
        ];
    }

    private function activateUser(User $user): array
    {
        $user->restore();
        $user->is_active = true;
        $user->save();

        $this->resendInvitation($user);
        $this->logActivityUpdate($user, ['is_active' => false]);

        return [
            'user'            => $user->fresh(),
            'cancelled_count' => 0,
            'action'          => 'activated',
        ];
    }

    private function resendInvitation(User $user): void
    {
        try {
            $token      = Str::random(64);
            $invitation = Invitation::where('email', $user->email)->whereNull('accepted_at')->first();

            if ($invitation) {
                $invitation->token      = $token;
                $invitation->expires_at = now()->addDays(7);
                $invitation->save();
            } else {
                Invitation::create([
                    'email'      => $user->email,
                    'role'       => $user->role,
                    'token'      => $token,
                    'expires_at' => now()->addDays(7),
                ]);
            }

            $user->notify(new UserInvited($token, $user->role));
        } catch (\Exception $e) {
            Log::error('Erro ao reenviar convite: ' . $e->getMessage());
        }
    }
}
