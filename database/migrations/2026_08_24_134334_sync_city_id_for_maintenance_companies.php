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
        // 1. Sync city_id from users to maintenance_companies if null
        $companies = \App\Models\MaintenanceCompany::whereNull('city_id')->get();
        foreach ($companies as $company) {
            if ($company->user && $company->user->city_id) {
                $company->update(['city_id' => $company->user->city_id]);
            }
        }

        // 2. Sync city_id from maintenance_companies to users if null
        $companies = \App\Models\MaintenanceCompany::whereNotNull('city_id')->get();
        foreach ($companies as $company) {
            if ($company->user && is_null($company->user->city_id)) {
                $company->user->update(['city_id' => $company->city_id]);
            }
        }

        // 3. For any company where city_id is still null, try to detect from address text
        $companies = \App\Models\MaintenanceCompany::whereNull('city_id')->get();
        foreach ($companies as $company) {
            if ($company->address) {
                $detectedCityId = \App\Models\City::detectFromText($company->address);
                if ($detectedCityId) {
                    $company->update(['city_id' => $detectedCityId]);
                    if ($company->user) {
                        $company->user->update(['city_id' => $detectedCityId]);
                    }
                }
            }
        }

        // 4. For any general User where city_id is null, try to detect from address text
        $users = \App\Models\User::whereNull('city_id')->get();
        foreach ($users as $user) {
            if ($user->address) {
                $detectedCityId = \App\Models\City::detectFromText($user->address);
                if ($detectedCityId) {
                    $user->update(['city_id' => $detectedCityId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
