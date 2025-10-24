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
            'name' => 'Admin User',
            'email' => 'admin@travelmanagement.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'Regular User',
            'email' => 'user@travelmanagement.com',
            'password' => Hash::make('user123'),
            'is_admin' => false,
        ]);
    }
}
