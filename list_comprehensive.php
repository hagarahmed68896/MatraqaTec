<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\Technician;

echo "--- USERS ---\n";
foreach (User::all() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Phone: [{$u->phone}] | Type: {$u->type}\n";
}

echo "\n--- COMPANIES ---\n";
foreach (MaintenanceCompany::all() as $c) {
    echo "ID: {$c->id} | User ID: {$c->user_id} | Name: {$c->company_name_en}\n";
}

echo "\n--- TECHNICIANS ---\n";
foreach (Technician::all() as $t) {
    echo "ID: {$t->id} | User ID: {$t->user_id} | Company ID: {$t->maintenance_company_id} | Name Ar: [{$t->name_ar}]\n";
}
