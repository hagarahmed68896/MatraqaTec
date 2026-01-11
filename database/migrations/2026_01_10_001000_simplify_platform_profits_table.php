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
        Schema::table('platform_profits', function (Blueprint $table) {
            $table->dropColumn(['type', 'note', 'percentage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_profits', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->string('note')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
        });
    }
};
