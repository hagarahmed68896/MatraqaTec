<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicianRequest;
use Illuminate\Http\Request;

class TechnicianRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicianRequest::with(['maintenanceCompany.user', 'service', 'category'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->get();
        return response()->json(['status' => true, 'message' => 'Requests retrieved', 'data' => $requests]);
    }

    public function show($id)
    {
        $techRequest = TechnicianRequest::with(['maintenanceCompany.user', 'service', 'category'])->find($id);
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Request retrieved', 'data' => $techRequest]);
    }

    public function update(Request $request, $id)
    {
        $techRequest = TechnicianRequest::find($id);
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        
        $techRequest->update($request->all());

        return response()->json(['status' => true, 'message' => 'Request updated', 'data' => $techRequest]);
    }

    public function destroy($id)
    {
        $techRequest = TechnicianRequest::find($id);
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $techRequest->delete();
        return response()->json(['status' => true, 'message' => 'Request deleted']);
    }
    public function accept(Request $request, $id)
    {
        $techRequest = TechnicianRequest::find($id);
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        if ($techRequest->status !== 'pending') {
            return response()->json(['status' => false, 'message' => 'Request already processed'], 422);
        }

        // 1. Create User
        $user = \App\Models\User::create([
            'name' => $techRequest->name_ar ?? $techRequest->name,
            'email' => $techRequest->email,
            'phone' => $techRequest->phone,
            'password' => \Illuminate\Support\Facades\Hash::make('tech@123'), // Default password
            'type' => 'technician',
            'avatar' => $techRequest->photo,
        ]);

        // 2. Create Technician Profile
        $technician = \App\Models\Technician::create([
            'user_id' => $user->id,
            'maintenance_company_id' => $techRequest->maintenance_company_id,
            'category_id' => $techRequest->category_id,
            'service_id' => $techRequest->service_id,
            'years_experience' => $techRequest->years_experience,
            'name_ar' => $techRequest->name_ar,
            'name_en' => $techRequest->name_en,
            'bio_ar' => $techRequest->bio_ar,
            'bio_en' => $techRequest->bio_en,
            'image' => $techRequest->photo,
            'national_id' => $techRequest->iqama_photo,
            'districts' => $techRequest->districts,
        ]);

        $techRequest->update(['status' => 'accepted']);

        return response()->json([
            'status' => true, 
            'message' => 'Request accepted and technician profile created', 
            'data' => [
                'user' => $user,
                'technician' => $technician
            ]
        ]);
    }

    public function refuse(Request $request, $id)
    {
        $techRequest = TechnicianRequest::find($id);
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $techRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return response()->json(['status' => true, 'message' => 'Request refused successfully', 'data' => $techRequest]);
    }
}
