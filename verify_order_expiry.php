<?php

use App\Models\Order;
use App\Models\User;
use App\Models\Technician;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set duration to 20 for testing
Setting::setByKey('order_acceptance_duration', 20, 'platform');

// Get an order
$order = Order::first();
if (!$order) {
    echo "No orders found.\n";
    exit;
}

// Ensure assigned_at is set for the test
$order->assigned_at = now()->subMinutes(5);
$order->save();

// Re-fetch to get appended attributes
$order = Order::find($order->id);

echo "Order Acceptance Duration: " . $order->acceptance_duration_minutes . " minutes\n";
echo "Assigned At: " . $order->assigned_at . "\n";
echo "Expiry Time: " . $order->acceptance_expiry_time . "\n";

$diff = $order->assigned_at->diffInMinutes($order->acceptance_expiry_time);
echo "Diff: $diff minutes (Expected: 20)\n";

if ($diff == 20) {
    echo "SUCCESS: Expiry calculation is correct.\n";
} else {
    echo "FAILURE: Expiry calculation mismatch.\n";
}

// Check JSON array
$json = $order->toArray();
if (isset($json['acceptance_expiry_time'])) {
    echo "SUCCESS: acceptance_expiry_time present in toArray().\n";
} else {
    echo "FAILURE: acceptance_expiry_time missing in toArray().\n";
}
