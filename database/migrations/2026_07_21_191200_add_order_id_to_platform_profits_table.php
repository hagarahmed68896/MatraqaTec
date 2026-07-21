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
        if (!Schema::hasColumn('platform_profits', 'order_id')) {
            Schema::table('platform_profits', function (Blueprint $table) {
                $table->foreignId('order_id')->nullable()->after('id')->constrained('orders')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('platform_profits', 'order_id')) {
            Schema::table('platform_profits', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            });
        }
    }
};
