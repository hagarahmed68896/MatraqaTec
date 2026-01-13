
// Clean up previous test user
\App\Models\User::where('email', 'phone_test@example.com')->delete();

$controller = new \App\Http\Controllers\Api\AuthController();

$data = [
    'name' => 'Phone Test',
    'email' => 'phone_test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'type' => 'individual',
    'phone' => '+966500000000' // Phone with +
];

$request = \Illuminate\Http\Request::create('/api/register', 'POST', $data);

echo "Registering user with phone: {$data['phone']}\n";

$response = $controller->register($request);

$content = $response->getData(true);

if (!$content['status']) {
    echo "Registration failed: " . $content['message'] . "\n";
    if (isset($content['errors'])) print_r($content['errors']);
    exit;
}

$user = \App\Models\User::where('email', 'phone_test@example.com')->first();

echo "User registered. Saved Phone: '{$user->phone}'\n";

if ($user->phone === '966500000000') {
    echo "SUCCESS: Phone number sanitized correctly.\n";
} else {
    echo "FAILED: Phone number was not sanitized. Value: {$user->phone}\n";
}
