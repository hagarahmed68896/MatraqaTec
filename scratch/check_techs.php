<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Technician;
use App\Models\User;

$allTechs = Technician::with('user')->get();
echo "Total Technicians in table: " . $allTechs->count() . "\n\n";

foreach ($allTechs as $index => $tech) {
    echo "Technician ID: " . $tech->id . "\n";
    echo "Name (AR): " . ($tech->name_ar ?? $tech->name ?? 'None') . "\n";
    echo "Maintenance Company ID: " . ($tech->maintenance_company_id ?? 'NULL') . "\n";
    if ($tech->user) {
        echo "User ID: " . $tech->user->id . "\n";
        echo "User Type: " . $tech->user->type . "\n";
        echo "User Status: " . $tech->user->status . "\n";
        echo "User Phone: " . $tech->user->phone . "\n";
    } else {
        echo "User relation is NULL!\n";
    }
    echo "-----------------------------------------\n";
}
