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
            $table->text('service_ids')->nullable()->after('service_id');
        });

        Schema::table('technician_requests', function (Blueprint $table) {
            $table->text('service_ids')->nullable()->after('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropColumn('service_ids');
        });

        Schema::table('technician_requests', function (Blueprint $table) {
            $table->dropColumn('service_ids');
        });
    }
};
