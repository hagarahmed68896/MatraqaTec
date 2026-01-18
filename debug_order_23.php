<?php

use App\Models\Order;
use App\Models\MaintenanceCompany;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orderId = 23;
$order = Order::find($orderId);

if (!$order) {
    echo "Order $orderId does NOT exist in the database.\n";
    exit;
}

echo "Order $orderId Details:\n";
echo "- Status: {$order->status}\n";
echo "- Maintenance Company ID: " . ($order->maintenance_company_id ?: 'NULL') . "\n";
echo "- Service ID: {$order->service_id}\n";
echo "- City ID: {$order->city_id}\n";

if ($order->maintenance_company_id) {
    $company = MaintenanceCompany::with('user')->find($order->maintenance_company_id);
    if ($company) {
        echo "- Assigned Company: {$company->company_name_ar} (User ID: {$company->user_id})\n";
    }
} else {
    echo "- WARNING: This order is NOT assigned to any maintenance company.\n";
}

echo "\nNote: For a Maintenance Company to 'accept' an order via the API, the order MUST already have their maintenance_company_id assigned by an admin or the system.\n";
