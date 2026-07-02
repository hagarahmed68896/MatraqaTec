<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Resolve user_id from corporate_customers
        $corporateContracts = DB::table('contracts')
            ->whereNull('user_id')
            ->whereNotNull('corporate_customer_id')
            ->get();

        foreach ($corporateContracts as $contract) {
            $corp = DB::table('corporate_customers')
                ->where('id', $contract->corporate_customer_id)
                ->first();
            if ($corp && $corp->user_id) {
                DB::table('contracts')
                    ->where('id', $contract->id)
                    ->update(['user_id' => $corp->user_id]);
            }
        }

        // 2. Resolve user_id from maintenance_companies
        $companyContracts = DB::table('contracts')
            ->whereNull('user_id')
            ->whereNotNull('maintenance_company_id')
            ->get();

        foreach ($companyContracts as $contract) {
            $comp = DB::table('maintenance_companies')
                ->where('id', $contract->maintenance_company_id)
                ->first();
            if ($comp && $comp->user_id) {
                DB::table('contracts')
                    ->where('id', $contract->id)
                    ->update(['user_id' => $comp->user_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse action needed
    }
};
