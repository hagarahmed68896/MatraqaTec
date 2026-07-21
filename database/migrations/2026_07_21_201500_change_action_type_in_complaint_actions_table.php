<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaint_actions', function (Blueprint $table) {
            $table->string('action_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaint_actions', function (Blueprint $table) {
            $table->enum('action_type', ['warning', 'suspension', 'rejection', 'clarification', 'refund'])->change();
        });
    }
};
