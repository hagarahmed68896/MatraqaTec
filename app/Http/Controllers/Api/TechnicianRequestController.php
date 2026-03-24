<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicianRequest;
use App\Models\User;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TechnicianRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        
        $query = TechnicianRequest::with(['category', 'service']);

        // 1. Ownership: Maintenance Company can see requests assigned to them or their name
        if ($user->type === 'maintenance_company') {
            $company = $user->maintenanceCompany;
            $query->where(function($q) use ($company) {
                $q->where('maintenance_company_id', $company->id)
                  ->orWhere('company_name', 'like', "%{$company->company_name_ar}%")
                  ->orWhere('company_name', 'like', "%{$company->company_name_en}%");
            });
        } elseif ($user->type === 'admin') {
            // Admin sees all
        } else {
            // Individual user (Technician candidate) sees only their own
            $query->where('user_id', $user->id);
        }

        // 2. Search Logic (Name, Phone, Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 3. Filters
        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category Filter (Parent Service)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Service Type Filter (Child Service)
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Districts Filter (JSON column search)
        if ($request->filled('district_ids')) {
            $districtIds = is_array($request->district_ids) ? $request->district_ids : explode(',', $request->district_ids);
            $query->where(function($q) use ($districtIds) {
                foreach ($districtIds as $id) {
                    $q->orWhereJsonContains('districts', (int)$id);
                }
            });
        }

        $requests = $query->latest()->paginate($request->input('per_page', 15));
        
        $locale = app()->getLocale();
        $requests->getCollection()->each(function($r) use ($locale) {
            if ($r->districts && is_array($r->districts)) {
                $r->district_names = \App\Models\District::whereIn('id', $r->districts)
                    ->pluck($locale == 'ar' ? 'name_ar' : 'name_en')
                    ->toArray();
            } else {
                $r->district_names = [];
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Requests retrieved successfully',
            'data' => $requests
        ]);
    }

    public function store(Request $request)
    {
        // Public or User. If User logged in, force ID.
        if (auth()->check()) {
            $request->merge(['user_id' => auth()->id()]);
        }

        $data = $request->all();

        // Handle File Uploads
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/technician_requests/photos'), $fileName);
            $data['photo'] = 'uploads/technician_requests/photos/' . $fileName;
        }
        if ($request->hasFile('image')) { // Some clients might use 'image' instead of 'photo'
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/technician_requests/photos'), $fileName);
            $data['photo'] = 'uploads/technician_requests/photos/' . $fileName;
        }
        if ($request->hasFile('iqama_photo')) {
            $file = $request->file('iqama_photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/technician_requests/iqamas'), $fileName);
            $data['iqama_photo'] = 'uploads/technician_requests/iqamas/' . $fileName;
        }

        $techRequest = TechnicianRequest::create($data);
        return response()->json(['status' => true, 'message' => 'Request submitted successfully', 'data' => $techRequest]);
    }

    public function show($id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $query = TechnicianRequest::with(['category', 'service']);

        if ($user->type === 'maintenance_company') {
            $company = $user->maintenanceCompany;
            $query->where('maintenance_company_id', $company->id);
        } elseif ($user->type === 'admin') {
            // Admin sees all
        } else {
            $query->where('user_id', $user->id);
        }

        $techRequest = $query->find($id);

        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $locale = app()->getLocale();
        if ($techRequest->districts && is_array($techRequest->districts)) {
            $techRequest->district_names = \App\Models\District::whereIn('id', $techRequest->districts)
                ->pluck($locale == 'ar' ? 'name_ar' : 'name_en')
                ->toArray();
        } else {
            $techRequest->district_names = [];
        }

        return response()->json(['status' => true, 'message' => 'Request retrieved', 'data' => $techRequest]);
    }

    public function update(Request $request, $id)
    {
         // Users usually don't update requests once submitted, maybe cancel.
         // Restricting to Admin mostly.
         return response()->json(['status' => false, 'message' => 'Update not allowed'], 403);
    }
}
