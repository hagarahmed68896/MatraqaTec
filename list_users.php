<?php

use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$users = User::all();
foreach ($users as $user) {
    echo "ID: {$user->id} | Name: {$user->name} | Phone: [{$user->phone}] | Email: [{$user->email}]\n";
}
