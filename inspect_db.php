<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "--- User Model Inspection ---\n";
$user = User::find(35);
if ($user) {
    echo json_encode($user->toArray(), JSON_PRETTY_PRINT) . "\n";
} else {
    echo "User 35 not found via Eloquent\n";
}

echo "\n--- Raw Database Row Inspection ---\n";
$row = DB::table('users')->where('id', 35)->first();
if ($row) {
    echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "User 35 not found via DB::table\n";
}

echo "\n--- Users Table Columns ---\n";
$columns = DB::select("SHOW COLUMNS FROM users");
foreach ($columns as $column) {
    echo "{$column->Field} ({$column->Type})\n";
}
