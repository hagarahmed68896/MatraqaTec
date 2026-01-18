<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Technician;
use App\Models\MaintenanceCompany;

echo "--- Relationships ---\n";
foreach (Technician::all() as $t) {
    if ($t->maintenance_company_id) {
        $c = MaintenanceCompany::find($t->maintenance_company_id);
        echo "Tech ID: {$t->id} | Name Ar: [{$t->name_ar}] | Belongs to Co ID: {$t->maintenance_company_id} | Co Owner User ID: " . ($c->user_id ?? 'N/A') . "\n";
    } else {
        echo "Tech ID: {$t->id} | Name Ar: [{$t->name_ar}] | NO COMPANY\n";
    }
}
