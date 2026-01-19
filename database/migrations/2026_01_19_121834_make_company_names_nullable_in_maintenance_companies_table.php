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
        Schema::table('maintenance_companies', function (Blueprint $table) {
            $table->string('company_name_ar')->nullable()->change();
            $table->string('company_name_en')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_companies', function (Blueprint $table) {
            $table->string('company_name_ar')->nullable(false)->change();
            $table->string('company_name_en')->nullable(false)->change();
        });
    }
};
