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
        Schema::table('technician_requests', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('id');
            $table->string('name_ar')->nullable()->after('name_en');
            $table->text('bio_en')->nullable()->after('years_experience');
            $table->text('bio_ar')->nullable()->after('bio_en');
            $table->string('iqama_photo')->nullable()->after('photo');
            $table->foreignId('maintenance_company_id')->nullable()->after('company_name')->constrained('maintenance_companies')->onDelete('cascade');
            $table->json('districts')->nullable()->after('maintenance_company_id');
            // category_id is basically service_id (parent)
            $table->foreignId('category_id')->nullable()->after('service_id')->constrained('services')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_requests', function (Blueprint $table) {
            //
        });
    }
};
