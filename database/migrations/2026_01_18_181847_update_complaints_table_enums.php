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
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('account_type')->change();
            $table->string('type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->enum('account_type', ['client', 'company', 'technician'])->change();
            $table->enum('type', ['general_inquiry', 'complaint_technician', 'complaint_client', 'payment_issue', 'suggestion_note'])->change();
        });
    }
};
