<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\CorporateCustomer;

echo "Fixing missing MaintenanceCompany profiles for existing users...\n";

$users = User::whereIn('type', ['maintenance_company', 'corporate_company'])->get();

foreach ($users as $user) {
    if (!$user->maintenanceCompany) {
        echo "Creating MaintenanceCompany profile for User ID: {$user->id} ({$user->email})...\n";
        
        // Try to get data from CorporateCustomer if it exists
        $corp = CorporateCustomer::where('user_id', $user->id)->first();
        
        MaintenanceCompany::create([
            'user_id' => $user->id,
            'company_name_en' => $corp->company_name_en ?? $user->name,
            'company_name_ar' => $corp->company_name_ar ?? $user->name,
            'commercial_record_number' => $corp->commercial_record_number ?? 'FIXED',
            'commercial_record_file' => $corp->commercial_record_file ?? null,
            'tax_number' => $corp->tax_number ?? 'FIXED',
            'address' => $corp->address ?? 'FIXED',
            'city_id' => 1,
        ]);
        echo "Done.\n";
    }
}

echo "Cleanup complete.\n";
