<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\Technician;

$techUser = User::where('name', 'like', '%محمد الفني%')->first();
if ($techUser) {
    echo "Found User: {$techUser->name} (ID: {$techUser->id})\n";
    $tech = Technician::where('user_id', $techUser->id)->first();
    if ($tech) {
        echo "Technician ID: {$tech->id} | Company ID: {$tech->maintenance_company_id} | Name Ar: [{$tech->name_ar}] | Name En: [{$tech->name_en}]\n";
    } else {
        echo "User exists but has no Technician profile.\n";
    }
} else {
    echo "User 'محمد الفني' not found in users table.\n";
    // Try searching name_ar in technicians table directly
    $tech = Technician::where('name_ar', 'like', '%محمد الفني%')->first();
    if ($tech) {
        echo "Found via Technician.name_ar: ID: {$tech->id} | Company ID: {$tech->maintenance_company_id} | Name Ar: [{$tech->name_ar}]\n";
    } else {
        echo "No technician found with that name in name_ar either.\n";
    }
}
