<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\Technician;
use Illuminate\Http\Request;

$controller = new \App\Http\Controllers\Api\MaintenanceCompanyController();

// Mock User 31 (Company 6)
$user = User::find(31);
$company = MaintenanceCompany::where('user_id', $user->id)->first();

echo "Testing Company ID: {$company->id}\n";

function testFilter($controller, $user, $params, $title) {
    echo "\n--- $title ---\n";
    $request = new Request($params);
    $request->setUserResolver(fn() => $user);
    $response = $controller->listTechnicians($request);
    $data = json_decode($response->getContent(), true);
    echo "Params: " . json_encode($params) . "\n";
    echo "Found " . count($data['data']) . " technicians.\n";
    foreach ($data['data'] as $t) {
        echo " - ID: {$t['id']} | Category: {$t['category_id']} | Service: {$t['service_id']} | Availability: {$t['availability_status']} | Districts: " . json_encode($t['districts']) . "\n";
    }
}

// 1. Test Category Filter
testFilter($controller, $user, ['category_ids' => '1'], "Category Filter (String 1)");
testFilter($controller, $user, ['category_ids' => [1]], "Category Filter (Array [1])");

// 2. Test District Filter
testFilter($controller, $user, ['district_ids' => '6'], "District Filter (String 6)");
testFilter($controller, $user, ['district_ids' => [7]], "District Filter (Array [7])");

// 3. Test Availability Filter
testFilter($controller, $user, ['availability' => 'available'], "Availability Filter (available)");

// 4. Test Rating Filter
// Need to ensure there is a review for tech 9
\App\Models\Review::updateOrCreate(
    ['technician_id' => 9, 'user_id' => 12],
    ['rating' => 5, 'comment' => 'Great!']
);
testFilter($controller, $user, ['min_rating' => 4], "Rating Filter (Min 4)");
