<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;

$user = User::find(35);
if ($user) {
    echo "ID: " . $user->id . "\n";
    echo "Phone: [" . $user->phone . "]\n";
    echo "Length: " . strlen($user->phone) . "\n";
    echo "Hex: " . bin2hex($user->phone) . "\n";
    
    // Check if password matches 'password123'
    $match = password_verify('password123', $user->password);
    echo "Password matches 'password123': " . ($match ? 'YES' : 'NO') . "\n";
    
    // Check if password matches 'password'
    $match2 = password_verify('password', $user->password);
    echo "Password matches 'password': " . ($match2 ? 'YES' : 'NO') . "\n";
} else {
    echo "User 35 not found\n";
}
