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
        Schema::create('company_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_company_id')->constrained('maintenance_companies')->onDelete('cascade');
            $table->string('day'); // 'Sunday', 'Monday', etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_schedules');
    }
};
