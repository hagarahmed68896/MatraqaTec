<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\Technician;
use Illuminate\Http\Request;

$controller = new \App\Http\Controllers\Api\MaintenanceCompanyController();
$user = User::find(31);

function testFilter($controller, $user, $params, $title) {
    echo "\n--- $title ---\n";
    $request = Request::create('/api/company/technicians', 'GET', $params);
    $request->setUserResolver(fn() => $user);
    $response = $controller->listTechnicians($request);
    $data = json_decode($response->getContent(), true);
    echo "Found " . count($data['data'] ?? []) . " technicians.\n";
}

try {
    // Ensure at least one review exists for rating test
    $order = \App\Models\Order::where('technician_id', 9)->first();
    if ($order) {
        \App\Models\Review::updateOrCreate(
            ['technician_id' => 9, 'user_id' => 12, 'order_id' => $order->id],
            ['rating' => 5, 'comment' => 'Great!']
        );
    }

    testFilter($controller, $user, ['category_ids' => [1]], "Category Check");
    testFilter($controller, $user, ['district_ids' => [6]], "District Check");
    testFilter($controller, $user, ['availability' => 'available'], "Availability Check");
    testFilter($controller, $user, ['min_rating' => 4], "Rating Check (Min 4)");
    testFilter($controller, $user, ['min_rating' => 6], "Rating Check (Min 6 - should be 0)");
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
