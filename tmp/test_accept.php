<?php

use App\Models\TechnicianRequest;
use App\Models\User;
use App\Models\Technician;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Find a pending request
$techRequest = TechnicianRequest::where('status', 'pending')->first();

if (!$techRequest) {
    echo "NO PENDING REQUEST FOUND\n";
    exit;
}

echo "Testing acceptance for request ID: " . $techRequest->id . " (" . $techRequest->email . ")\n";

try {
    DB::beginTransaction();

    // Check for existing user
    $normalizedPhone = $techRequest->phone;
    $existingUser = User::where('email', $techRequest->email)
        ->orWhere('phone', $normalizedPhone)
        ->first();

    if ($existingUser) {
        echo "ERROR: User already exists ID: " . $existingUser->id . "\n";
        DB::rollBack();
        exit;
    }

    // 1. Create User
    $user = User::create([
        'name' => $techRequest->name_ar ?? $techRequest->name,
        'email' => $techRequest->email,
        'phone' => $techRequest->phone,
        'password' => Hash::make('password123'),
        'type' => 'technician',
        'avatar' => $techRequest->photo,
        'status' => 'active',
    ]);

    echo "User created ID: " . $user->id . "\n";

    // 2. Create Technician Profile
    $technician = Technician::create([
        'user_id' => $user->id,
        'maintenance_company_id' => $techRequest->maintenance_company_id,
        'category_id' => $techRequest->category_id,
        'service_id' => $techRequest->service_id,
        'years_experience' => $techRequest->years_experience,
        'name' => $techRequest->name ?? $techRequest->name_ar,
        'name_ar' => $techRequest->name_ar,
        'name_en' => $techRequest->name_en,
        'bio_ar' => $techRequest->bio_ar,
        'bio_en' => $techRequest->bio_en,
        'image' => $techRequest->photo,
        'national_id_image' => $techRequest->iqama_photo,
        'districts' => $techRequest->districts,
    ]);

    echo "Technician created ID: " . $technician->id . "\n";

    $techRequest->update(['status' => 'accepted']);
    echo "Request status updated to accepted\n";

    DB::commit();
    echo "SUCCESS: Transaction committed\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "TRACE: " . $e->getTraceAsString() . "\n";
}
