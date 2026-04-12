<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'cancelled' to the appointments status ENUM
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled'");

        // Sync old data: mark appointments as 'completed' if their order is completed
        DB::statement("UPDATE appointments a JOIN orders o ON a.order_id = o.id SET a.status = 'completed' WHERE o.status = 'completed' AND a.status = 'scheduled'");

        // Sync old data: mark appointments as 'cancelled' if their order is cancelled/rejected
        DB::statement("UPDATE appointments a JOIN orders o ON a.order_id = o.id SET a.status = 'cancelled' WHERE o.status IN ('cancelled', 'rejected') AND a.status = 'scheduled'");
    }

    public function down(): void
    {
        // Revert appointments back to 'scheduled' before removing the ENUM value
        DB::statement("UPDATE appointments SET status = 'scheduled' WHERE status = 'cancelled'");
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled', 'in_progress', 'completed') DEFAULT 'scheduled'");
    }
};
