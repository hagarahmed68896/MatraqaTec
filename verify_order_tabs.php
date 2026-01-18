<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\Order;
use Illuminate\Http\Request;

$controller = new \App\Http\Controllers\Api\OrderController();
$user = User::find(31); // Company 6

echo "Testing Company Order Tabs\n";
echo "==========================\n\n";

function testTab($controller, $user, $tab, $title) {
    echo "--- $title ---\n";
    $request = Request::create('/api/orders', 'GET', ['tab' => $tab]);
    $request->setUserResolver(fn() => $user);
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['status']) {
        $count = count($data['data']['data'] ?? []);
        echo "Found $count orders\n";
        foreach (($data['data']['data'] ?? []) as $order) {
            echo " - Order #{$order['order_number']} | Status: {$order['status']} | Company: " . ($order['maintenance_company_id'] ?? 'None') . "\n";
        }
    } else {
        echo "Error: " . $data['message'] . "\n";
    }
    echo "\n";
}

testTab($controller, $user, 'all', 'All Orders Tab');
testTab($controller, $user, 'new', 'New Orders Tab');
testTab($controller, $user, 'in_progress', 'In Progress Tab');
testTab($controller, $user, null, 'Default Tab');

// Test order details
echo "--- Order Details Test ---\n";
$order = Order::where('maintenance_company_id', 6)->orWhere(function($q) {
    $q->where('status', 'new')->where('city_id', 1);
})->first();

if ($order) {
    $request = Request::create("/api/orders/{$order->id}", 'GET');
    $request->setUserResolver(fn() => $user);
    $response = $controller->show($order->id);
    $data = json_decode($response->getContent(), true);
    
    if ($data['status']) {
        echo "Order #{$data['data']['order_number']}\n";
        echo "Status: {$data['data']['status']}\n";
        echo "Client: {$data['data']['user']['name']}\n";
        echo "Service: {$data['data']['service']['name_ar']}\n";
    }
} else {
    echo "No orders found for testing\n";
}
