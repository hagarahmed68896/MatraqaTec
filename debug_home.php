<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Http\Request;

function checkHomeForUser($email) {
    echo "--- Checking Home for $email ---\n";
    $user = User::where('email', $email)->first();
    if (!$user) {
        echo "User not found.\n";
        return;
    }
    echo "User Type: {$user->type}\n";
    
    $request = Request::create('/api/home', 'GET');
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    $controller = new HomeController();
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    echo "Data Keys: " . implode(', ', array_keys($data['data'])) . "\n";
    
    if (isset($data['data']['top_technicians'])) {
        echo "Top Technicians Count: " . count($data['data']['top_technicians']) . "\n";
    } else {
        echo "TOP TECHNICIANS MISSING\n";
    }
    
    if (isset($data['data']['statistics'])) {
        echo "Statistics: " . json_encode($data['data']['statistics']) . "\n";
    } else {
        echo "STATISTICS MISSING\n";
    }

    if (isset($data['data']['recent_orders'])) {
        echo "Recent Orders Count: " . count($data['data']['recent_orders']) . "\n";
    } else {
        echo "RECENT ORDERS MISSING\n";
    }
    echo "\n";
}

// Check a maintenance company
$maintCompany = User::where('type', 'maintenance_company')->first();
if ($maintCompany) checkHomeForUser($maintCompany->email);

// Check a corporate company
$corpCompany = User::where('type', 'corporate_company')->first();
if ($corpCompany) checkHomeForUser($corpCompany->email);
