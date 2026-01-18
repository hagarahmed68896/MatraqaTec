<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\MaintenanceCompanyController;
use Illuminate\Http\Request;

echo "Finding user...\n";
$user = User::where('email', 'contact@techsolutions.sa')->first();

if (!$user) {
    echo "User not found.\n";
    exit(1);
}

echo "User found: ID={$user->id}, Type='{$user->type}'\n";

// Mock Request
$request = Request::create('/api/company/technicians', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

echo "Invoking controller...\n";
$controller = new MaintenanceCompanyController();
try {
    $response = $controller->listTechnicians($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Content: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
