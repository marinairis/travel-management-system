<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TravelRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'helena.duarte@empresa.com'],
            ['name' => 'Helena Duarte', 'password' => Hash::make('Admin@1234'), 'role' => 'admin']
        );

        $manager = User::firstOrCreate(
            ['email' => 'ana.ferreira@empresa.com'],
            ['name' => 'Ana Ferreira', 'password' => Hash::make('Manager@1234'), 'role' => 'manager']
        );

        $requester1 = User::firstOrCreate(
            ['email' => 'carlos.silva@empresa.com'],
            ['name' => 'Carlos Silva', 'password' => Hash::make('Requester@1234'), 'role' => 'requester']
        );

        $requester2 = User::firstOrCreate(
            ['email' => 'joao.santos@empresa.com'],
            ['name' => 'João Santos', 'password' => Hash::make('Requester@5678'), 'role' => 'requester']
        );

        $this->seedTravelRequests($requester1, $admin);
        $this->seedTravelRequests($requester2, $manager);
        $this->seedTravelRequests($admin, null);
        $this->seedTravelRequests($manager, $admin);
    }

    private function seedTravelRequests(User $user, ?User $approver): void
    {
        $now = Carbon::now();

        TravelRequest::firstOrCreate(
            ['user_id' => $user->id, 'destination' => 'São Paulo, SP'],
            [
                'requester_name' => $user->name,
                'departure_date' => $now->copy()->addDays(10)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(13)->format('Y-m-d'),
                'status' => 'requested',
                'approved_by' => null,
                'approved_at' => null,
                'notes' => 'Reunião com cliente',
                'travel_type' => 'plane',
            ]
        );

        TravelRequest::firstOrCreate(
            ['user_id' => $user->id, 'destination' => 'Rio de Janeiro, RJ'],
            [
                'requester_name' => $user->name,
                'departure_date' => $now->copy()->addDays(20)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(23)->format('Y-m-d'),
                'status' => $approver ? 'approved' : 'requested',
                'approved_by' => $approver?->id,
                'approved_at' => $approver ? $now->copy()->subDays(2) : null,
                'notes' => 'Conferência de tecnologia',
                'travel_type' => 'bus',
            ]
        );
    }
}
