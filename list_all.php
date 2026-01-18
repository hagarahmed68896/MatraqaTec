<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MaintenanceCompany;
use App\Models\Technician;
use App\Models\User;

echo "--- Companies ---\n";
foreach (MaintenanceCompany::all() as $c) {
    $u = User::find($c->user_id);
    echo "ID: " . $c->id . " | User ID: " . $c->user_id . " | Name: " . $c->company_name_en . " | Phone: " . ($u->phone ?? 'N/A') . "\n";
}

echo "\n--- Technicians for Company 6 ---\n";
foreach (Technician::where('maintenance_company_id', 6)->get() as $t) {
    $u = User::find($t->user_id);
    echo "ID: " . $t->id . " | User ID: " . $t->user_id . " | Name Ar: [" . ($t->name_ar ?? 'N/A') . "] | User Name: [" . ($u->name ?? 'N/A') . "] | Phone: " . ($u->phone ?? 'N/A') . "\n";
}
