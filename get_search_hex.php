<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SearchHistory;

$sh = SearchHistory::find(7);
echo "Query: " . $sh->query . "\n";
echo "Hex: " . bin2hex($sh->query) . "\n";
