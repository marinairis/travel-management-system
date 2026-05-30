<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@travelmanagement.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Gestor Viagens',
            'email' => 'gestor@travelmanagement.com',
            'password' => Hash::make('Gestor@123'),
            'role' => 'manager',
        ]);

        User::create([
            'name' => 'Solicitante Demo',
            'email' => 'solicitante@travelmanagement.com',
            'password' => Hash::make('Solicit@1'),
            'role' => 'requester',
        ]);
    }
}
