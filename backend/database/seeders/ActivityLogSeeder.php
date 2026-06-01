<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\TravelRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $manager = User::where('role', 'manager')->first();
        $requester = User::where('role', 'requester')->first();

        $requests = TravelRequest::all();

        foreach ($requests as $tr) {
            $creator = User::find($tr->user_id);

            ActivityLog::create([
                'user_id' => $creator->id,
                'action' => 'create',
                'model_type' => 'App\\Models\\TravelRequest',
                'model_id' => $tr->id,
                'description' => "Pedido de viagem criado: {$tr->destination}",
                'new_values' => json_encode([
                    'destination' => $tr->destination,
                    'departure_date' => $tr->departure_date,
                    'return_date' => $tr->return_date,
                    'status' => 'requested',
                ]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'created_at' => $tr->created_at,
            ]);

            if ($tr->status === 'approved' && $tr->approved_by) {
                $approver = User::find($tr->approved_by);
                ActivityLog::create([
                    'user_id' => $approver->id,
                    'action' => 'status_change',
                    'model_type' => 'App\\Models\\TravelRequest',
                    'model_id' => $tr->id,
                    'description' => "Status alterado para aprovado: {$tr->destination}",
                    'old_values' => json_encode(['status' => 'requested']),
                    'new_values' => json_encode(['status' => 'approved']),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder',
                    'created_at' => $tr->approved_at ?? Carbon::now()->subDays(1),
                ]);
            }

            if ($tr->status === 'cancelled') {
                $canceller = $tr->approved_by ? User::find($tr->approved_by) : $creator;
                ActivityLog::create([
                    'user_id' => $canceller->id,
                    'action' => 'cancel',
                    'model_type' => 'App\\Models\\TravelRequest',
                    'model_id' => $tr->id,
                    'description' => "Pedido cancelado: {$tr->destination}",
                    'old_values' => json_encode(['status' => 'requested']),
                    'new_values' => json_encode(['status' => 'cancelled']),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Seeder',
                    'created_at' => Carbon::now()->subDays(rand(1, 5)),
                ]);
            }
        }

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'create',
            'model_type' => 'App\\Models\\User',
            'model_id' => $manager->id,
            'description' => "Usuário criado: {$manager->email}",
            'new_values' => json_encode(['name' => $manager->name, 'role' => 'manager']),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'create',
            'model_type' => 'App\\Models\\User',
            'model_id' => $requester->id,
            'description' => "Usuário criado: {$requester->email}",
            'new_values' => json_encode(['name' => $requester->name, 'role' => 'requester']),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
        ]);
    }
}
