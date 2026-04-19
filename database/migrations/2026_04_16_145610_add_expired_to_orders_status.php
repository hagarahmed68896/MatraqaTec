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
        // For MySQL, changing enum requires re-declaring all values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'accepted', 'in_progress', 'completed', 'rejected', 'cancelled', 'scheduled', 'expired') NOT NULL DEFAULT 'new'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('new', 'accepted', 'in_progress', 'completed', 'rejected', 'cancelled', 'scheduled') NOT NULL DEFAULT 'new'");
    }
};
