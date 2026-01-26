<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\Technician;
use App\Models\Order;

$user = User::find(31);
$company = MaintenanceCompany::where('user_id', $user->id)->first();
echo "Logged in as User: {$user->id} | Company: " . ($company->id ?? 'N/A') . "\n";

$queryStr = "محمد السباك";
echo "Searching for: [$queryStr]\n";

$technicians = Technician::with(['user'])
    ->where('maintenance_company_id', $company->id)
    ->where(function($q) use ($queryStr) {
        $q->where('name_ar', 'like', "%{$queryStr}%")
          ->orWhere('name_en', 'like', "%{$queryStr}%")
          ->orWhereHas('user', function($q2) use ($queryStr) {
              $q2->where('name', 'like', "%{$queryStr}%")
                ->orWhere('phone', 'like', "%{$queryStr}%");
          });
    })
    
    ->get();

echo "Found " . $technicians->count() . " technicians.\n";
foreach ($technicians as $t) {
    echo "ID: {$t->id} | Name Ar: [{$t->name_ar}] | User Name: [{$t->user->name}]\n";
}

