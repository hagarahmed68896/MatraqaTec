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
            $table->string('bank_name')->nullable()->after('districts');
            $table->string('account_name')->nullable()->after('bank_name');
            $table->string('account_number')->nullable()->after('account_name');
            $table->string('bank_address')->nullable()->after('account_number');
            $table->string('iban')->nullable()->after('bank_address');
            $table->string('swift_code')->nullable()->after('iban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'account_name', 'account_number', 'bank_address', 'iban', 'swift_code']);
        });
    }
};
