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
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('corporate_customer_id')->nullable()->after('maintenance_company_id')->constrained('corporate_customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->after('corporate_customer_id')->constrained('users')->nullOnDelete();
            $table->foreignId('maintenance_company_id')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['corporate_customer_id']);
            $table->dropColumn('corporate_customer_id');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('maintenance_company_id')->nullable(false)->change();
        });

    }
};
