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
        Schema::create('maintenance_company_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_company_id')->constrained('maintenance_companies')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('maintenance_company_district', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_company_id')->constrained('maintenance_companies')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_company_pivots');
    }
};
