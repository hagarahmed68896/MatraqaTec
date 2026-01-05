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
        Schema::table('contents', function (Blueprint $table) {
            // First drop the old image column if it exists, or rename/modify it.
            // Since we want multiple images, JSON is best.
            // Safe approach: drop 'image' and add 'images'.
            $table->dropColumn('image');
            $table->json('images')->nullable()->after('description_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('images');
            $table->string('image')->nullable()->after('description_en');
        });
    }
};
