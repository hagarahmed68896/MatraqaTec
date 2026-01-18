<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MaintenanceCompany;
use App\Models\Technician;
use Illuminate\Http\Request;

$controller = new \App\Http\Controllers\Api\MaintenanceCompanyController();
$user = User::find(31);

function testFilter($controller, $user, $params, $title) {
    echo "\n--- $title ---\n";
    $request = Request::create('/api/company/technicians', 'GET', $params);
    $request->setUserResolver(fn() => $user);
    $response = $controller->listTechnicians($request);
    $data = json_decode($response->getContent(), true);
    echo "Params: " . json_encode($params) . "\n";
    echo "Found " . count($data['data'] ?? []) . " technicians.\n";
    foreach ($data['data'] ?? [] as $t) {
        echo " - ID: {$t['id']} | Cat: {$t['category_id']} | Svc: {$t['service_id']}\n";
    }
}

// Tech 9 has Category 1, Service 3
echo "Base Data: Tech 9 is Cat 1, Svc 3\n";

// 1. Single Category
testFilter($controller, $user, ['category_id' => 1], "Single Category (1)");

// 2. Multi Service
testFilter($controller, $user, ['service_ids' => [3, 4]], "Multi Service ([3, 4])");

// 3. Category + Service
testFilter($controller, $user, ['category_id' => 1, 'service_ids' => [3]], "Category 1 + Svc 3");
testFilter($controller, $user, ['category_id' => 1, 'service_ids' => [4]], "Category 1 + Svc 4 (Should be 0 if tech 9 is svc 3)");

// Global Search Check
echo "\n--- Global Service Search Check ---\n";
$svcController = new \App\Http\Controllers\Api\ServiceController();
$request = Request::create('/api/services', 'GET', ['category_id' => 1]);
$response = $svcController->index($request);
$data = json_decode($response->getContent(), true);
echo "Category 1 Search Found: " . count($data['data']['results'] ?? []) . " services.\n";
