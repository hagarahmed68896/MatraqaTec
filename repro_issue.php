<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://127.0.0.1:8000/api';

echo "Attempting login...\n";
$response = Http::post("$baseUrl/login", [
    'email' => 'contact@techsolutions.sa',
    'password' => 'password',
]);

if ($response->failed()) {
    echo "Login failed (password): " . $response->body() . "\n";
    $response = Http::post("$baseUrl/login", [
        'email' => 'contact@techsolutions.sa',
        'password' => '12345678',
    ]);
}

if ($response->successful()) {
    $data = $response->json();
    $token = $data['data']['token'] ?? $data['token'] ?? null;
    
    if (!$token) {
        echo "Token not found in response: " . json_encode($data) . "\n";
        exit;
    }

    echo "Token received.\n";
    
    $techResponse = Http::withToken($token)->get("$baseUrl/company/technicians");
    echo "Endpoint Status: " . $techResponse->status() . "\n";
    echo "Endpoint Body: " . $techResponse->body() . "\n";
} else {
    echo "All logins failed.\n";
}
