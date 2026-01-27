<?php
 
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
 
use App\Models\User;
use App\Models\Notification;
use App\Http\Controllers\Api\OrderController;
 
echo "--- Starting Verification ---\n";
ob_start();
 
// 1. Setup a test user
$user = User::firstOrCreate(['email' => 'test_settings@example.com'], [
    'name' => 'Test Settings',
    'password' => bcrypt('password'),
    'phone' => '123456789',
    'type' => 'individual'
]);
 
echo "User ID: {$user->id}\n";
 
// 2. Test initial settings
echo "Initial Notifications: " . ($user->notification_enabled ? "Enabled" : "Disabled") . "\n";
 
// 3. Test update settings
$user->notification_enabled = false;
$user->save();
$user->refresh();
echo "Updated Notifications: " . ($user->notification_enabled ? "Enabled" : "Disabled") . "\n";
 
// 4. Test Notification Logic
echo "Testing notification suppression...\n";
$initialCount = Notification::where('user_id', $user->id)->count();
 
// Simulate sending notification
// We need to use a proxy to call the private method or just test the logic inside sendNotification
$orderController = new OrderController();
$reflection = new ReflectionClass($orderController);
$method = $reflection->getMethod('sendNotification');
$method->setAccessible(true);
 
$method->invokeArgs($orderController, [$user->id, [
    'type' => 'test',
    'title_ar' => 'Test AR',
    'title_en' => 'Test EN',
    'body_ar' => 'Body AR',
    'body_en' => 'Body EN',
    'data' => []
]]);
 
$newCount = Notification::where('user_id', $user->id)->count();
echo "Notification sent when disabled? Count change: " . ($newCount - $initialCount) . " (Expected: 0)\n";
 
// 5. Enable and test again
$user->notification_enabled = true;
$user->save();
 
$method->invokeArgs($orderController, [$user->id, [
    'type' => 'test',
    'title_ar' => 'Test AR',
    'title_en' => 'Test EN',
    'body_ar' => 'Body AR',
    'body_en' => 'Body EN',
    'data' => []
]]);
 
$finalCount = Notification::where('user_id', $user->id)->count();
echo "Notification sent when enabled? Count change: " . ($finalCount - $newCount) . " (Expected: 1)\n";
 
// Cleanup
$user->delete();
echo "--- Verification Complete ---\n";
$output = ob_get_clean();
echo $output;
file_put_contents('verify_results.log', $output);
