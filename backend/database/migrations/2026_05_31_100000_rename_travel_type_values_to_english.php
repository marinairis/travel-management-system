<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('travel_requests')->where('travel_type', 'aereo')->update(['travel_type' => 'plane']);
        DB::table('travel_requests')->where('travel_type', 'onibus')->update(['travel_type' => 'bus']);
        DB::table('travel_requests')->where('travel_type', 'carro')->update(['travel_type' => 'car']);
    }

    public function down(): void
    {
        DB::table('travel_requests')->where('travel_type', 'plane')->update(['travel_type' => 'aereo']);
        DB::table('travel_requests')->where('travel_type', 'bus')->update(['travel_type' => 'onibus']);
        DB::table('travel_requests')->where('travel_type', 'car')->update(['travel_type' => 'carro']);
    }
};
