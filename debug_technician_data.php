<?php

use App\Models\Order;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find the latest order that has a technician assigned
$order = Order::whereNotNull('technician_id')->latest()->first();

if (!$order) {
    echo "No order found with a technician assigned.\n";
    exit;
}

echo "Order ID: " . $order->id . "\n";
echo "Technician ID: " . $order->technician_id . "\n";

$order->load(['technician.user']);

if ($order->technician) {
    echo "Technician found.\n";
    echo "Technician Name (direct): " . ($order->technician->name ?? 'N/A') . "\n";
    echo "Technician User ID: " . $order->technician->user_id . "\n";
    
    if ($order->technician->user) {
        echo "Technician User found.\n";
        echo "Technician User Name: " . $order->technician->user->name . "\n";
        echo "Technician User Avatar: " . ($order->technician->user->avatar ?? 'None') . "\n";
    } else {
        echo "Technician User NOT found (relation is null).\n";
    }
} else {
    echo "Technician relation is NULL despite technician_id being set.\n";
}

echo "\nChecking Maintenance Company...\n";
if ($order->maintenance_company_id) {
    echo "Maintenance Company ID: " . $order->maintenance_company_id . "\n";
    $order->load(['maintenanceCompany.user']);
    // ... similar checks
} else {
    echo "No Maintenance Company assigned.\n";
}
