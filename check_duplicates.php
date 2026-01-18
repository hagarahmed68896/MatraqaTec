<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$phone = '511223344';
$users = User::where('phone', $phone)->get();
echo "Searching for phone: $phone\n";
foreach ($users as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Type: {$u->type}\n";
}
