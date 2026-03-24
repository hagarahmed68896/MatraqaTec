<?php
use App\Models\MaintenanceCompany;
use App\Models\Order;
use App\Models\Service;

$orderId = Order::latest()->first()->id ?? 1;
$order = Order::with('service')->find($orderId);

if (!$order) {
    echo "No orders found.\n";
    exit;
}

echo "Testing for Order ID: {$order->id}\n";
echo "Order City ID: " . ($order->city_id ?? 'NULL') . "\n";
echo "Order Service ID: {$order->service_id}\n";
$categoryId = $order->service->parent_id ?? $order->service_id;
echo "Order Category ID: {$categoryId}\n";

$companies = MaintenanceCompany::all();
echo "Total Companies: " . $companies->count() . "\n";

foreach ($companies as $c) {
    $user = $c->user;
    echo "--- Company ID: {$c->id} ---\n";
    echo "Name: " . ($user->name ?? 'N/A') . "\n";
    echo "User Status: " . ($user->status ?? 'N/A') . "\n";
    echo "Company City ID: " . ($c->city_id ?? 'NULL') . "\n";
    echo "User City ID: " . ($user->city_id ?? 'NULL') . "\n";
    
    $hasService = $c->services()->where('services.id', $order->service_id)->exists();
    $hasCategory = $c->services()->where('services.id', $categoryId)->exists();
    echo "Has Order Service: " . ($hasService ? 'YES' : 'NO') . "\n";
    echo "Has Order Category: " . ($hasCategory ? 'YES' : 'NO') . "\n";
    
    // Check specific query conditions
    $cityMatch = ($c->city_id == $order->city_id);
    $statusActive = ($user && $user->status === 'active');
    $serviceMatch = ($hasService || $hasCategory);
    
    echo "Condition - City Match: " . ($cityMatch ? 'PASS' : 'FAIL') . "\n";
    echo "Condition - Status Active: " . ($statusActive ? 'PASS' : 'FAIL') . "\n";
    echo "Condition - Service Match: " . ($serviceMatch ? 'PASS' : 'FAIL') . "\n";
}
