<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\CorporateCustomer;
use Illuminate\Support\Facades\DB;

echo "--- Unified Company Migration ---\n";

DB::transaction(function() {
    // 1. Find all corporate_company users
    $users = User::where('type', 'corporate_company')->get();
    echo "Found " . $users->count() . " corporate_company users.\n";

    foreach ($users as $user) {
        echo "Processing User ID: {$user->id} ({$user->email})...\n";

        // 2. Ensure MaintenanceCompany profile exists
        $maintProfile = MaintenanceCompany::where('user_id', $user->id)->first();
        $corpProfile = CorporateCustomer::where('user_id', $user->id)->first();

        if (!$maintProfile) {
            echo "  Creating MaintenanceCompany profile...\n";
            MaintenanceCompany::create([
                'user_id' => $user->id,
                'company_name_en' => $corpProfile->company_name_en ?? $user->name,
                'company_name_ar' => $corpProfile->company_name_ar ?? $user->name,
                'commercial_record_number' => $corpProfile->commercial_record_number ?? 'MIGRATED',
                'commercial_record_file' => $corpProfile->commercial_record_file ?? null,
                'tax_number' => $corpProfile->tax_number ?? 'MIGRATED',
                'address' => $corpProfile->address ?? 'MIGRATED',
                'city_id' => 1, // Defaulting to 1 if unknown
            ]);
        } else {
            echo "  MaintenanceCompany profile already exists.\n";
        }

        // 3. Update User type
        $user->type = 'maintenance_company';
        $user->save();
        echo "  User type updated to maintenance_company.\n";
    }
});

echo "--- Migration Complete ---\n";
