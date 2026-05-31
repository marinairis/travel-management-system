<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@travelmanagement.com'],
            ['name' => 'Admin Sistema', 'password' => Hash::make('Admin@123'), 'role' => 'admin']
        );

        User::firstOrCreate(
            ['email' => 'gestor@travelmanagement.com'],
            ['name' => 'Gestor Viagens', 'password' => Hash::make('Gestor@123'), 'role' => 'manager']
        );

        User::firstOrCreate(
            ['email' => 'solicitante@travelmanagement.com'],
            ['name' => 'Solicitante Demo', 'password' => Hash::make('Solicit@1'), 'role' => 'requester']
        );

        User::firstOrCreate(
            ['email' => 'ana.costa@travelmanagement.com'],
            ['name' => 'Ana Costa', 'password' => Hash::make('AnaCosta@1'), 'role' => 'requester']
        );
    }
}
