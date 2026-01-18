<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::create('/api/login', 'POST', [
    'phone' => '0511223344',
    'password' => 'password123'
]));

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

// Mimic AuthController@login logic
if ($request->filled('phone')) {
    $phone = $request->phone;
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
    if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
    $request->merge(['phone' => $phone]);
}

echo "Normalized Phone in Request: " . $request->phone . "\n";

$credentials = ['password' => $request->password];
if ($request->filled('email')) {
    $credentials['email'] = $request->email;
} elseif ($request->filled('phone')) {
    $credentials['phone'] = $request->phone;
}

echo "Credentials to Auth::attempt: " . json_encode($credentials) . "\n";

if (!Auth::attempt($credentials)) {
    echo "Auth::attempt FAILED\n";
    $userExists = User::where($request->filled('email') ? 'email' : 'phone', $request->filled('email') ? $request->email : $request->phone)->exists();
    echo "UserExists debug check: " . ($userExists ? 'TRUE' : 'FALSE') . "\n";
    
    // Check with direct where
    $direct = User::where('phone', '511223344')->exists();
    echo "Direct User::where('phone', '511223344')->exists(): " . ($direct ? 'TRUE' : 'FALSE') . "\n";
} else {
    echo "Auth::attempt SUCCESS\n";
}
