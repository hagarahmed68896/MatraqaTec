<?php

use App\Models\User;
use Illuminate\Support\Facades\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function simulateLogin($phone, $password, $requiredType = null) {
    echo "Simulating Login: Phone=$phone, RequiredType=" . json_encode($requiredType) . "\n";
    
    $requestData = [
        'phone' => $phone,
        'password' => $password
    ];
    if ($requiredType) {
        $requestData['required_type'] = $requiredType;
    }

    $request = \Illuminate\Http\Request::create('/api/login', 'POST', $requestData);
    $controller = app(\App\Http\Controllers\Api\AuthController::class);
    
    $response = $controller->login($request);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body: " . $response->getContent() . "\n\n";
}

// 1. Create temporary test users if not exist
$client = User::updateOrCreate(
    ['phone' => '500000001'],
    ['name' => 'Test Client', 'email' => 'client@test.com', 'password' => bcrypt('password'), 'type' => 'individual']
);

$tech = User::updateOrCreate(
    ['phone' => '500000002'],
    ['name' => 'Test Technician', 'email' => 'tech@test.com', 'password' => bcrypt('password'), 'type' => 'technician']
);

echo "--- START TEST --- \n\n";

// Scenario 1: Client logging in to Client App (Success)
simulateLogin('500000001', 'password', 'individual');

// Scenario 2: Client logging in to Tech App (Fail)
simulateLogin('500000001', 'password', 'technician');

// Scenario 3: Technician logging in to Tech App (Success)
simulateLogin('500000002', 'password', 'technician');

// Scenario 4: Technician logging in to Client App (Fail)
simulateLogin('500000002', 'password', 'individual');

// Scenario 5: Login without required_type (Backward compatibility check)
simulateLogin('500000001', 'password');

echo "--- END TEST --- \n";

// Cleanup test users (optional, but keep for verification)
// $client->delete();
// $tech->delete();
