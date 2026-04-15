    <?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TechnicianRequest;

$requests = TechnicianRequest::take(5)->get(['id', 'photo', 'iqama_photo']);
echo json_encode($requests, JSON_PRETTY_PRINT);
