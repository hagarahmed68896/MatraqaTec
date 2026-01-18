<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\Technician;

// Mock User 31
$user = User::find(31);
if (!$user) die("User 31 not found\n");

$company = MaintenanceCompany::where('user_id', $user->id)->first();
echo "Testing as User ID: {$user->id} (Company ID: {$company->id})\n";

$queryStr = "محمد";
echo "Searching for: [{$queryStr}]\n";

$request = Illuminate\Http\Request::create('/api/company/search', 'GET', ['query' => $queryStr]);
$request->setUserResolver(fn() => $user);

$controller = new \App\Http\Controllers\Api\MaintenanceCompanyController();
$response = $controller->search($request);

echo "Response Content:\n";
echo $response->getContent() . "\n";

// Check if Muhammad Tech is found
$data = json_decode($response->getContent(), true);
if (!empty($data['data']['technicians'])) {
    echo "SUCCESS: Found technician in results!\n";
    foreach ($data['data']['technicians'] as $t) {
        echo " - " . ($t['name_ar'] ?? 'N/A') . " (ID: {$t['id']})\n";
    }
} else {
    echo "FAILURE: Technician not found in results.\n";
}
