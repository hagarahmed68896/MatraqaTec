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
            $table->decimal('project_value', 15, 2)->nullable()->change();
            $table->string('status')->default('pending')->change();
            
            $table->string('company_name')->nullable()->after('user_id');
            $table->string('commercial_record_number')->nullable()->after('company_name');
            $table->string('head_office')->nullable()->after('commercial_record_number');
            $table->string('contact_number')->nullable()->after('head_office');
            $table->text('service_description')->nullable()->after('contact_number');
            
            $table->string('boq_file')->nullable()->after('service_description');
            $table->string('drawings_file')->nullable()->after('boq_file');
            $table->string('other_docs_file')->nullable()->after('drawings_file');
            
            $table->string('price_offer_file')->nullable()->after('other_docs_file');
            $table->string('signed_contract_file')->nullable()->after('contract_file');
            $table->text('rejection_reason')->nullable()->after('signed_contract_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('project_value', 15, 2)->nullable(false)->change();
            $table->string('status')->default('active')->change();
            
            $table->dropColumn([
                'company_name',
                'commercial_record_number',
                'head_office',
                'contact_number',
                'service_description',
                'boq_file',
                'drawings_file',
                'other_docs_file',
                'price_offer_file',
                'signed_contract_file',
                'rejection_reason'
            ]);
        });
    }
};
