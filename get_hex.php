<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Technician;

$t = Technician::find(9);
echo "Name Ar: " . $t->name_ar . "\n";
echo "Hex: " . bin2hex($t->name_ar) . "\n";
