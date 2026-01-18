<?php

use App\Models\User;
use App\Models\Service;
use App\Models\City;
use App\Models\District;
use App\Models\MaintenanceCompany;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function test_search_and_filter() {
    echo "--- Testing Search History ---\n";
    $user = User::where('type', 'individual')->first();
    if (!$user) {
        echo "No individual user found to test search history.\n";
    } else {
        $searchQuery = "Electricity " . rand(100, 999);
        auth()->login($user);
        
        $response = (new \App\Http\Controllers\Api\ServiceController())->index(new \Illuminate\Http\Request(['search' => $searchQuery]));
        
        $history = SearchHistory::where('user_id', $user->id)->where('query', $searchQuery)->first();
        if ($history) {
            echo "Successfully recorded search history for query: $searchQuery\n";
        } else {
            echo "FAILED to record search history.\n";
        }
    }

    echo "\n--- Testing Advanced Filters ---\n";
    $request = new \Illuminate\Http\Request([
        'category_ids' => [1, 2],
        'min_rating' => 4,
        'availability' => 'available'
    ]);
    
    try {
        $response = (new \App\Http\Controllers\Api\ServiceController())->index($request);
        $data = json_decode($response->getContent(), true);
        echo "Filter request successful. Results count: " . ($data['data']['results_count'] ?? 0) . "\n";
    } catch (\Exception $e) {
        echo "Filter request FAILED: " . $e->getMessage() . "\n";
    }
}

function test_notifications() {
    echo "\n--- Testing Notification Triggers ---\n";
    $company = MaintenanceCompany::with('services')->first();
    $client = User::where('type', 'individual')->first();
    
    if ($company && $client && $company->services->isNotEmpty()) {
        $service = $company->services->first();
        $cityId = $company->city_id ?? City::first()->id; 
        auth()->login($client);
        
        echo "Creating test order for Service ID: {$service->id} and City ID: {$cityId}...\n";
        
        $order = \App\Models\Order::create([
            'order_number' => 'TEST-' . rand(1000, 9999),
            'user_id' => $client->id,
            'service_id' => $service->id,
            'city_id' => $city->id,
            'status' => 'new',
            'base_price' => 100,
            'total_price' => 115,
            'payment_method' => 'cash',
            'scheduled_at' => now()->addDays(2),
            'address' => 'Test Address'
        ]);

        // Manually trigger the notification helper for testing
        $controller = new \App\Http\Controllers\Api\OrderController();
        $reflector = new ReflectionClass($controller);
        $method = $reflector->getMethod('notifyNewOrder');
        $method->setAccessible(true);
        $method->invokeArgs($controller, [$order]);

        $notification = \App\Models\Notification::where('user_id', $company->user_id)
            ->where('type', \App\Models\Notification::TYPE_NEW_ORDER)
            ->where('data', 'like', "%\"order_id\":{$order->id}%")
            ->latest()
            ->first();

        if ($notification) {
            echo "Successfully triggered NEW_ORDER notification for company user (ID: {$company->user_id}).\n";
        } else {
            echo "FAILED to trigger NEW_ORDER notification. Checked User ID: {$company->user_id}\n";
            $potential = \App\Models\MaintenanceCompany::whereHas('services', function($q) use ($service) {
                $q->where('services.id', $service->id);
            })->where('city_id', $city->id)->pluck('id');
            echo "Eligible companies for this service/city: " . $potential->implode(', ') . "\n";
        }
    } else {
        echo "Insufficient data to test notifications. Company: " . ($company?'Yes':'No') . ", Client: " . ($client?'Yes':'No') . ", Service: " . ($company && $company->services->isNotEmpty() ? 'Yes' : 'No') . "\n";
    }
}

test_search_and_filter();
test_notifications();
