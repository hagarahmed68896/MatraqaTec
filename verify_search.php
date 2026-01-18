<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Models\MaintenanceCompany;
use Illuminate\Support\Facades\Auth;

// Mock login as a company
$user = User::where('type', 'maintenance_company')->first();
if (!$user) {
    die("No company user found\n");
}

Auth::login($user);
echo "Logged in as: {$user->name} (ID: {$user->id})\n";

$company = $user->maintenanceCompany;
echo "Company ID: " . ($company->id ?? 'N/A') . "\n";

// Test Search for technician "محمد"
$request = Illuminate\Http\Request::create('/api/company/search', 'GET', ['query' => 'محمد']);
$request->setUserResolver(fn() => $user);

$controller = new \App\Http\Controllers\Api\MaintenanceCompanyController();
$response = $controller->search($request);

echo "Search Response:\n";
echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Test History
$responseHistory = $controller->getSearchHistory($request);
echo "History Response:\n";
echo json_encode(json_decode($responseHistory->getContent()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
