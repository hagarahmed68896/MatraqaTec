<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\CorporateCustomer;

echo "Checking for data inconsistencies...\n";

$users = User::whereIn('type', ['maintenance_company', 'corporate_company'])->get();

foreach ($users as $user) {
    $hasMaintenanceProfile = MaintenanceCompany::where('user_id', $user->id)->exists();
    $hasCorporateProfile = CorporateCustomer::where('user_id', $user->id)->exists();
    
    echo "User ID: {$user->id}, Email: {$user->email}, Type: {$user->type}\n";
    echo "  - Has MaintenanceCompany Profile: " . ($hasMaintenanceProfile ? 'YES' : 'NO') . "\n";
    echo "  - Has CorporateCustomer Profile: " . ($hasCorporateProfile ? 'YES' : 'NO') . "\n";
    
    if (!$hasMaintenanceProfile) {
        echo "  *** ORPHANED USER (Missing Company Profile) ***\n";
    }
}
