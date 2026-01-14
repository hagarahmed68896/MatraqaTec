
$email = 'city_test@example.com';
\App\Models\User::where('email', $email)->delete();

// Create user with NO city
$user = \App\Models\User::create([
    'name' => 'City Test',
    'email' => $email,
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'type' => 'individual',
    'city_id' => null, // Explicitly null
    'phone' => '511111111'
]);

\App\Models\IndividualCustomer::create(['user_id' => $user->id]);

\Illuminate\Support\Facades\Auth::login($user);
$controller = new \App\Http\Controllers\Api\ClientProfileController();

echo "Initial City ID: " . ($user->city_id ?? 'NULL') . "\n";

// Update with Riyadh Coordinates
$updateData = [
    'latitude' => 24.7136,
    'longitude' => 46.6753
];

$reqUpdate = \Illuminate\Http\Request::create('/api/profile/update', 'POST', $updateData);
$reqUpdate->setUserResolver(function () use ($user) { return $user; });

echo "Updating with Riyadh Coordinates (24.7136, 46.6753)...\n";
$controller->update($reqUpdate);

$user->refresh();

echo "Final City ID: " . ($user->city_id ?? 'NULL') . "\n";
echo "Final Address: " . $user->address . "\n";

if ($user->city_id == 1) { // Assuming ID 1 is Riyadh
    echo "SUCCESS: City updated correctly.\n";
} else {
    echo "FAIL: Expected City ID 1, got " . ($user->city_id ?? 'NULL') . "\n";
    
    // Debug info
    echo "Note: Ensure ID 1 is Riyadh in your DB.\n";
}

// Clean up
\App\Models\User::where('email', $email)->delete();
