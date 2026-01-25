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
        Schema::create('contract_payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('receipt_file');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'check', 'other'])->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_payment_receipts');
    }
};
