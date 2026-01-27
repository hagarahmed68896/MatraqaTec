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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notification_enabled')->default(true)->after('longitude');
            $table->boolean('night_mode')->default(false)->after('notification_enabled');
            $table->string('language')->default('ar')->after('night_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notification_enabled', 'night_mode', 'language']);
        });
    }
};
