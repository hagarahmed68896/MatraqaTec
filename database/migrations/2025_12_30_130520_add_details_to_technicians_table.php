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
        Schema::table('technicians', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_ar')->nullable();
            $table->string('image')->nullable();
            $table->string('national_id')->nullable(); // Adding here if missing in create
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar', 'bio_en', 'bio_ar', 'image', 'national_id']);
        });
    }
};
