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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['assigned_at', 'expires_at']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('reminded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dateTime('reminded_at')->nullable();
        });
    }
};
