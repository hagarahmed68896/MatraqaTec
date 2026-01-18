<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Http\Request;

echo "--- Final Verification: Unified Company System ---\n";

// 1. Check for any leftover corporate_company users
$orphans = User::where('type', 'corporate_company')->count();
echo "Leftover corporate_company users: $orphans\n";

// 2. Check Home Dashboard for a company
$company = User::where('type', 'maintenance_company')->first();
if ($company) {
    echo "Testing Home Dashboard for: {$company->email}\n";
    $request = Request::create('/api/home', 'GET');
    $request->setUserResolver(function () use ($company) { return $company; });
    $controller = new HomeController();
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    if (isset($data['data']['recent_orders'])) {
        echo "SUCCESS: recent_orders found (Count: " . count($data['data']['recent_orders']) . ")\n";
    } else {
        echo "FAILURE: recent_orders NOT found\n";
    }
    
    if (isset($data['data']['top_technicians'])) {
        echo "SUCCESS: top_technicians found\n";
    } else {
        echo "FAILURE: top_technicians NOT found\n";
    }
}

echo "--- Verification Complete ---\n";
