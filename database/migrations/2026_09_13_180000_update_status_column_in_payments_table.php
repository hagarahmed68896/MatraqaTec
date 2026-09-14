<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter status column on payments table to varchar(50) to allow 'refunded', 'completed', 'paid', etc.
        try {
            DB::statement("ALTER TABLE `payments` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Fallback via Schema Builder if supported
            Schema::table('payments', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `payments` MODIFY COLUMN `status` ENUM('completed', 'pending', 'failed', 'incomplete') NOT NULL DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Ignore on rollback
        }
    }
};
