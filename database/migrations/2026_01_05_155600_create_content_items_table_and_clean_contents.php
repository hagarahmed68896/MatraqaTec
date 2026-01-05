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
        // 1. Create content_items table
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->string('title_ar')->nullable();
            $table->string('title_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('button_text_ar')->nullable();
            $table->string('button_text_en')->nullable();
            $table->timestamps();
        });

        // 2. Clean up contents table (remove child-specific fields)
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn([
                'description_ar', 
                'description_en', 
                'images', // Dropping the JSON column we added earlier
                'button_text_ar', 
                'button_text_en'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_items');

        Schema::table('contents', function (Blueprint $table) {
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->json('images')->nullable();
            $table->string('button_text_ar')->nullable();
            $table->string('button_text_en')->nullable();
        });
    }
};
