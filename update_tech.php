<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Technician;

$tech = Technician::find(10);
if ($tech) {
    $tech->update([
        'service_id' => 1,
        'category_id' => 1,
        'availability_status' => 'available'
    ]);
    echo "SUCCESS: Technician #10 updated for testing.\n";
} else {
    echo "ERROR: Technician #10 not found.\n";
}
