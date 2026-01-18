<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$orphans = User::whereIn('type', ['maintenance_company', 'corporate_company'])
    ->doesntHave('maintenanceCompany')
    ->get(['id', 'email', 'type', 'phone']);

echo "Found " . $orphans->count() . " orphaned users (missing MaintenanceCompany profile):\n";
foreach ($orphans as $u) {
    echo "ID: {$u->id} | Email: {$u->email} | Phone: {$u->phone} | Type: {$u->type}\n";
}
