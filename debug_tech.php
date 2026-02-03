<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\Technician;
use App\Models\Service;

$orderId = 4;
$order = Order::find($orderId);

if (!$order) {
    echo "Order #$orderId not found.\n";
    exit;
}

echo "Order #$orderId details:\n";
echo "City ID: " . $order->city_id . "\n";
echo "Service ID: " . $order->service_id . "\n";

$service = Service::find($order->service_id);
if ($service) {
    $categoryId = $service->parent_id ?? $service->id;
    echo "Category ID: " . $categoryId . "\n";
} else {
    echo "Service not found.\n";
}

$techs = Technician::with('user')->get();
echo "\nTotal Technicians: " . $techs->count() . "\n";

foreach ($techs as $tech) {
    echo "-------------------\n";
    echo "Tech ID: " . $tech->id . "\n";
    echo "User ID: " . ($tech->user->id ?? 'N/A') . "\n";
    echo "User Status: " . ($tech->user->status ?? 'N/A') . "\n";
    echo "User City ID: " . ($tech->user->city_id ?? 'N/A') . "\n";
    echo "Availability: " . $tech->availability_status . "\n";
    echo "Tech Service ID: " . $tech->service_id . "\n";
    echo "Tech Category ID: " . $tech->category_id . "\n";
    
    $matchesCity = ($tech->user->city_id ?? null) == $order->city_id;
    $matchesService = $tech->service_id == $order->service_id || $tech->category_id == ($categoryId ?? null);
    $isAvailable = $tech->availability_status == 'available';
    $isActive = ($tech->user->status ?? '') == 'active';
    
    echo "Matches City: " . ($matchesCity ? 'YES' : 'NO') . "\n";
    echo "Matches Service: " . ($matchesService ? 'YES' : 'NO') . "\n";
    echo "Is Available Status: " . ($isAvailable ? 'YES' : 'NO') . "\n";
    echo "Is Active User: " . ($isActive ? 'YES' : 'NO') . "\n";
}
