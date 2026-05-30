<?php

namespace Database\Seeders;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TravelRequestSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $manager = User::where('role', 'manager')->first();
        $requester = User::where('role', 'requester')->first();

        $now = Carbon::now();

        $requests = [
            // Solicitados — aguardando aprovação
            [
                'user_id' => $requester->id,
                'requester_name' => $requester->name,
                'destination' => 'São Paulo, SP',
                'departure_date' => $now->copy()->addDays(10)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(14)->format('Y-m-d'),
                'status' => 'requested',
                'notes' => 'Reunião com cliente — Contrato Q3',
            ],
            [
                'user_id' => $requester->id,
                'requester_name' => $requester->name,
                'destination' => 'Belo Horizonte, MG',
                'departure_date' => $now->copy()->addDays(20)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(23)->format('Y-m-d'),
                'status' => 'requested',
                'notes' => 'Workshop de produto',
            ],
            [
                'user_id' => $requester->id,
                'requester_name' => $requester->name,
                'destination' => 'Curitiba, PR',
                'departure_date' => $now->copy()->addDays(30)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(32)->format('Y-m-d'),
                'status' => 'requested',
                'notes' => null,
            ],
            [
                'user_id' => $admin->id,
                'requester_name' => $admin->name,
                'destination' => 'Recife, PE',
                'departure_date' => $now->copy()->addDays(15)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(17)->format('Y-m-d'),
                'status' => 'requested',
                'notes' => 'Conferência de tecnologia',
            ],
            [
                'user_id' => $manager->id,
                'requester_name' => $manager->name,
                'destination' => 'Florianópolis, SC',
                'departure_date' => $now->copy()->addDays(25)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(28)->format('Y-m-d'),
                'status' => 'requested',
                'notes' => 'Visita a fornecedor',
            ],
            // Aprovados
            [
                'user_id' => $requester->id,
                'requester_name' => $requester->name,
                'destination' => 'Rio de Janeiro, RJ',
                'departure_date' => $now->copy()->addDays(5)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(8)->format('Y-m-d'),
                'status' => 'approved',
                'approved_by' => $manager->id,
                'approved_at' => $now->copy()->subDays(2),
                'notes' => 'Apresentação para investidores',
            ],
            [
                'user_id' => $requester->id,
                'requester_name' => $requester->name,
                'destination' => 'Brasília, DF',
                'departure_date' => $now->copy()->addDays(40)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(42)->format('Y-m-d'),
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => $now->copy()->subDays(1),
                'notes' => 'Reunião com parceiro governamental',
            ],
            [
                'user_id' => $admin->id,
                'requester_name' => $admin->name,
                'destination' => 'Porto Alegre, RS',
                'departure_date' => $now->copy()->addDays(60)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(63)->format('Y-m-d'),
                'status' => 'approved',
                'approved_by' => $manager->id,
                'approved_at' => $now->copy()->subDays(3),
                'notes' => null,
            ],
            [
                'user_id' => $manager->id,
                'requester_name' => $manager->name,
                'destination' => 'Salvador, BA',
                'departure_date' => $now->copy()->addDays(12)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(15)->format('Y-m-d'),
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => $now->copy()->subDays(4),
                'notes' => 'Treinamento regional',
            ],
            [
                'user_id' => $requester->id,
                'requester_name' => $requester->name,
                'destination' => 'Manaus, AM',
                'departure_date' => $now->copy()->addDays(50)->format('Y-m-d'),
                'return_date' => $now->copy()->addDays(54)->format('Y-m-d'),
                'status' => 'approved',
                'approved_by' => $manager->id,
                'approved_at' => $now->copy()->subDays(5),
                'notes' => 'Expansão de operações',
            ],
            // Cancelados
            [
                'user_id' => $requester->id,
                'requester_name' => $requester->name,
                'destination' => 'Fortaleza, CE',
                'departure_date' => $now->copy()->subDays(20)->format('Y-m-d'),
                'return_date' => $now->copy()->subDays(17)->format('Y-m-d'),
                'status' => 'cancelled',
                'notes' => 'Cancelado — reunião adiada',
            ],
            [
                'user_id' => $admin->id,
                'requester_name' => $admin->name,
                'destination' => 'Goiânia, GO',
                'departure_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'return_date' => $now->copy()->subDays(8)->format('Y-m-d'),
                'status' => 'cancelled',
                'approved_by' => $manager->id,
                'approved_at' => $now->copy()->subDays(15),
                'notes' => null,
            ],
            [
                'user_id' => $manager->id,
                'requester_name' => $manager->name,
                'destination' => 'Vitória, ES',
                'departure_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'return_date' => $now->copy()->subDays(3)->format('Y-m-d'),
                'status' => 'cancelled',
                'notes' => 'Cancelado por orçamento',
            ],
        ];

        foreach ($requests as $data) {
            TravelRequest::create($data);
        }
    }
}
