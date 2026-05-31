<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to modify the enum to include 'expired'
        // First, change to a string type temporarily to allow the new value
        DB::statement("ALTER TABLE travel_requests MODIFY COLUMN status VARCHAR(20) DEFAULT 'requested'");
        
        // Then add a CHECK constraint or keep it as VARCHAR
        // For compatibility, we'll keep it as VARCHAR since MySQL ENUM doesn't support adding values easily
        Schema::table('travel_requests', function (Blueprint $table) {
            // The column is already modified above, this is just to ensure the schema is correct
        });
    }

    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE travel_requests MODIFY COLUMN status ENUM('requested', 'approved', 'cancelled') DEFAULT 'requested'");
    }
};