<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\TechnicianRequest;
use App\Http\Controllers\Api\Admin\TechnicianRequestController;
use Illuminate\Http\Request;

echo "--- Verification: Admin Technician Request Approval ---\n";

// 1. Create a dummy request
$company = \App\Models\MaintenanceCompany::first();
$techRequest = TechnicianRequest::create([
    'name_ar' => 'فني معتمد',
    'name_en' => 'Approved Technician',
    'email' => 'approved_tech@example.com',
    'phone' => '0599999999',
    'photo' => 'tech_photos/1.jpg',
    'iqama_photo' => 'iqamas/1.jpg',
    'maintenance_company_id' => $company->id ?? 1,
    'category_id' => 1,
    'service_id' => 1,
    'years_experience' => 10,
    'bio_ar' => 'خبير',
    'bio_en' => 'Expert',
    'districts' => [1, 2],
    'status' => 'pending',
]);
echo "Created pending request ID: {$techRequest->id}\n";

// 2. Accept the request
$admin = User::where('type', 'admin')->first();
$acceptRequest = Request::create("/api/admin/technician-requests/{$techRequest->id}/accept", 'POST');
$acceptRequest->setUserResolver(function () use ($admin) { return $admin; });

$controller = new TechnicianRequestController();
$response = $controller->accept($acceptRequest, $techRequest->id);
$data = json_decode($response->getContent(), true);

if ($data['status']) {
    echo "SUCCESS: Request accepted.\n";
    
    // Check if user and technician profile created
    $user = User::where('email', 'approved_tech@example.com')->first();
    $technician = \App\Models\Technician::where('user_id', $user->id ?? 0)->first();
    
    if ($user && $technician && $technician->maintenance_company_id == $techRequest->maintenance_company_id) {
        echo "SUCCESS: User and Technician profile created and linked correctly.\n";
        echo "Districts in Technician: " . json_encode($technician->districts) . "\n";
    } else {
        echo "FAILURE: User or Technician profile missing or incorrect.\n";
    }
} else {
    echo "FAILURE: Acceptance failed.\n";
    print_r($data);
}

// 3. Test Refuse
$refuseRequestObj = TechnicianRequest::create([
    'name_ar' => 'فني مرفوض',
    'email' => 'refused_tech@example.com',
    'phone' => '0588888888',
    'status' => 'pending',
]);

$refuseRequest = Request::create("/api/admin/technician-requests/{$refuseRequestObj->id}/refuse", 'POST', [
    'rejection_reason' => 'Data incomplete'
]);
$refuseRequest->setUserResolver(function () use ($admin) { return $admin; });
$refuseResponse = $controller->refuse($refuseRequest, $refuseRequestObj->id);
$refuseData = json_decode($refuseResponse->getContent(), true);

if ($refuseData['status'] && $refuseData['data']['status'] == 'rejected') {
    echo "SUCCESS: Request refused with reason.\n";
} else {
    echo "FAILURE: Refusal failed.\n";
}

// Cleanup
$techRequest->delete();
$refuseRequestObj->delete();
if (isset($user)) {
    $technician->delete();
    $user->delete();
}

echo "--- Verification Complete ---\n";
