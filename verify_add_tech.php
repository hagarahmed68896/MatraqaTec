<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Service;
use App\Models\District;
use App\Http\Controllers\Api\MaintenanceCompanyController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

echo "--- Verification: Company Adding Technician ---\n";

// 1. Get a company user
$company = User::where('type', 'maintenance_company')->first();
if (!$company) {
    die("Error: No maintenance company found in DB\n");
}
echo "Testing for Company: {$company->email}\n";

// 2. Prepare mock data
$category = Service::whereNull('parent_id')->first();
$service = Service::where('parent_id', $category->id)->first() ?? $category;
$districts = District::take(2)->pluck('id')->toArray();

// Mock images
Storage::fake('public');
$image = UploadedFile::fake()->image('tech.jpg');
$iqama = UploadedFile::fake()->image('iqama.jpg');

$requestData = [
    'name_ar' => 'فني متقن',
    'name_en' => 'Expert Technician',
    'phone' => '0500000000',
    'email' => 'tech@example.com',
    'image' => $image,
    'iqama_photo' => $iqama,
    'category_id' => $category->id,
    'service_id' => $service->id,
    'districts' => $districts,
    'years_experience' => 5,
    'bio_ar' => 'خبير في الكهرباء',
    'bio_en' => 'Expert in electricity',
];

// 3. Call the controller
$request = Request::create('/api/company/technicians/add', 'POST', $requestData);
$request->files->set('image', $image);
$request->files->set('iqama_photo', $iqama);
$request->setUserResolver(function () use ($company) { return $company; });

$controller = new MaintenanceCompanyController();
$response = $controller->addTechnician($request);
$data = json_decode($response->getContent(), true);

if ($data['status']) {
    echo "SUCCESS: Technician request submitted.\n";
    echo "Message: " . $data['message'] . "\n";
    
    // 4. Verify DB
    $techRequest = \App\Models\TechnicianRequest::where('email', 'tech@example.com')->first();
    if ($techRequest && $techRequest->status == 'pending') {
        echo "SUCCESS: DB record found with status 'pending'.\n";
    } else {
        echo "FAILURE: DB record not found or status incorrect.\n";
    }
} else {
    echo "FAILURE: Submission failed.\n";
    print_r($data);
}

echo "--- Verification Complete ---\n";
