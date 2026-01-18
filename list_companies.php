<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\MaintenanceCompany;

$companies = MaintenanceCompany::all();
foreach ($companies as $c) {
    echo "Company ID: {$c->id} | User ID: {$c->user_id} | Name: {$c->company_name_en} | Techs: " . $c->technicians()->count() . "\n";
}
