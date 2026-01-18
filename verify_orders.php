<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;

echo "--- Verification: Company Order Management ---\n";

// 1. Get a company user
$company = User::where('type', 'maintenance_company')->first();
if (!$company) {
    die("Error: No maintenance company found in DB\n");
}
echo "Testing for Company: {$company->email}\n";

// 2. Clear old test orders and create a fresh 'new' order for this company
$companyProfile = $company->maintenanceCompany;
if (!$companyProfile) {
    die("Error: Company profile missing\n");
}

$testOrder = Order::create([
    'order_number' => 'TEST-NEW-' . time(),
    'user_id' => User::where('type', 'individual')->first()->id ?? 1,
    'maintenance_company_id' => $companyProfile->id,
    'service_id' => \App\Models\Service::whereNotNull('parent_id')->first()->id ?? 1,
    'city_id' => 1,
    'status' => 'new',
    'scheduled_at' => now()->addDay(),
    'address' => 'Test Address',
    'total_price' => 100,
    'payment_method' => 'cash'
]);
echo "Created test 'new' order: {$testOrder->order_number}\n";

// 3. Check Home Dashboard
$request = Request::create('/api/home', 'GET');
$request->setUserResolver(function () use ($company) { return $company; });
\Illuminate\Support\Facades\Auth::login($company);
$homeController = new HomeController();
$response = $homeController->index($request);
$homeData = json_decode($response->getContent(), true);

if (isset($homeData['data']['current_order']) && $homeData['data']['current_order']['id'] == $testOrder->id) {
    echo "SUCCESS: current_order correctly identified in Home dashboard\n";
} else {
    echo "FAILURE: current_order NOT found or unexpected\n";
    print_r($homeData['data']['current_order'] ?? 'NULL');
}

// 4. Test Accept
echo "Testing Accept Order...\n";
$acceptRequest = Request::create("/api/orders/{$testOrder->id}/accept", 'POST', [
    'scheduled_at' => now()->addDays(2)
]);
$acceptRequest->setUserResolver(function () use ($company) { return $company; });
$orderController = new OrderController();
$acceptResponse = $orderController->accept($acceptRequest, $testOrder->id);
$acceptData = json_decode($acceptResponse->getContent(), true);

if ($acceptData['status'] && $acceptData['data']['status'] == 'scheduled') {
    echo "SUCCESS: Order accepted and status updated to scheduled\n";
} else {
    echo "FAILURE: Order acceptance failed\n";
    print_r($acceptData);
}

// 5. Test Refuse (on another order)
$testOrder2 = Order::create([
    'order_number' => 'TEST-REFUSE-' . time(),
    'user_id' => $testOrder->user_id,
    'maintenance_company_id' => $companyProfile->id,
    'service_id' => $testOrder->service_id,
    'city_id' => 1,
    'status' => 'new',
    'scheduled_at' => now()->addDay(),
    'address' => 'Test Address 2',
    'total_price' => 100,
    'payment_method' => 'cash'
]);
echo "Created second test order for refusal: {$testOrder2->order_number}\n";

echo "Testing Refuse Order...\n";
$refuseRequest = Request::create("/api/orders/{$testOrder2->id}/refuse", 'POST', [
    'rejection_reason' => 'Test rejection reason'
]);
$refuseRequest->setUserResolver(function () use ($company) { return $company; });
$refuseResponse = $orderController->refuse($refuseRequest, $testOrder2->id);
$refuseData = json_decode($refuseResponse->getContent(), true);

if ($refuseData['status'] && $refuseData['data']['status'] == 'rejected') {
    echo "SUCCESS: Order refused and status updated to rejected\n";
} else {
    echo "FAILURE: Order refusal failed\n";
    print_r($refuseData);
}

// Cleanup
$testOrder->delete();
$testOrder2->delete();

echo "--- Verification Complete ---\n";
