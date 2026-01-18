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
        Schema::table('inventory', function (Blueprint $table) {
            $table->foreignId('maintenance_company_id')->nullable()->after('id')->constrained('maintenance_companies')->onDelete('cascade');
            $table->integer('quantity')->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropForeign(['maintenance_company_id']);
            $table->dropColumn(['maintenance_company_id', 'quantity']);
        });
    }
};
