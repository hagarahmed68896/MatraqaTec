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
        Schema::table('cities', function (Blueprint $table) {
            $table->string('region_ar')->nullable()->after('name_en');
            $table->string('region_en')->nullable()->after('region_ar');
        });

        Schema::table('maintenance_companies', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete()->after('id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete()->after('id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete()->after('id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['region_ar', 'region_en']);
        });

        Schema::table('maintenance_companies', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });
    }
};
