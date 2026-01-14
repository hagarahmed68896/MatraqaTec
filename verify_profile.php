
// 1. Setup Data
$email = 'profile_test@example.com';
\App\Models\User::where('email', $email)->delete();

$user = \App\Models\User::create([
    'name' => 'Old Name',
    'email' => $email,
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'type' => 'individual',
    'phone' => '500000000'
]);

\App\Models\IndividualCustomer::create([
    'user_id' => $user->id,
    'first_name_en' => 'OldFirst',
    'last_name_en' => 'OldLast'
]);

\Illuminate\Support\Facades\Auth::login($user);

$controller = new \App\Http\Controllers\Api\ClientProfileController();

echo "--- Initial Profile ---\n";
$reqShow = \Illuminate\Http\Request::create('/api/profile', 'GET');
$reqShow->setUserResolver(function () use ($user) { return $user; });
$resShow = $controller->show($reqShow);
$data = $resShow->getData(true)['data'];
echo "Name: " . $data['user']['name'] . "\n";

echo "\n--- Updating Profile ---\n";
// Update Request using POST (simulating mobile form)
$updateData = [
    'first_name_en' => 'NewFirst',
    'last_name_en' => 'NewLast',
    'phone' => '599999999',
    'address' => 'Riyadh, Olaya St',
    'latitude' => 24.7136,
    'longitude' => 46.6753
];
$reqUpdate = \Illuminate\Http\Request::create('/api/profile/update', 'POST', $updateData);
$reqUpdate->setUserResolver(function () use ($user) { return $user; });
$resUpdate = $controller->update($reqUpdate);

if (!$resUpdate->getData(true)['status']) {
    echo "Update Failed: " . $resUpdate->getData(true)['message'] . "\n";
    exit;
}

$user->refresh();
echo "New Name (User table): " . $user->name . "\n";
echo "New Phone: " . $user->phone . "\n";
echo "New Address: " . $user->address . "\n";
echo "New Location: " . $user->latitude . "," . $user->longitude . "\n";

echo "\n--- Changing Password ---\n";
$passData = [
    'current_password' => 'password123',
    'new_password' => 'newpassword123',
    'new_password_confirmation' => 'newpassword123'
];
$reqPass = \Illuminate\Http\Request::create('/api/profile/change-password', 'POST', $passData);
$reqPass->setUserResolver(function () use ($user) { return $user; });
$resPass = $controller->changePassword($reqPass);

echo "Password Change: " . ($resPass->getData(true)['status'] ? 'Success' : 'Failed') . "\n";

// Verify new password
if (\Illuminate\Support\Facades\Hash::check('newpassword123', $user->password)) {
    echo "Password Verified!\n";
} else {
    echo "Password Verification Failed.\n";
}

// Clean up
\App\Models\User::where('email', $email)->delete();
