<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\TechnicianAccountCreated;

class TechnicianRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicianRequest::with(['maintenanceCompany.user', 'service', 'category']);

        // 1. Status Filter (Tabs)
        $status = $request->get('status');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // 2. Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 3. Service Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 4. Service Type Filter
        if ($request->filled('service_id')) {
            if (is_array($request->service_id)) {
                $query->whereIn('service_id', $request->service_id);
            } else {
                $query->where('service_id', $request->service_id);
            }
        }

        // 5. Sorting Logic
        $sort = $request->get('sort_by', 'latest');
        switch ($sort) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $items = $query->paginate(20)->withQueryString();

        // Data for Filters
        $categories = \App\Models\Service::whereNull('parent_id')->get();
        $services = \App\Models\Service::whereNotNull('parent_id')->get();

        // Statistics
        $stats = [
            'total_requests' => TechnicianRequest::count(),
            'accepted_requests' => TechnicianRequest::where('status', 'accepted')->count(),
            'rejected_requests' => TechnicianRequest::where('status', 'rejected')->count(),
            'pending_requests' => TechnicianRequest::where('status', 'pending')->count(),
        ];

        return view('admin.technician_requests.index', compact('items', 'stats', 'categories', 'services'));
    }

    public function show($id)
    {
        $item = TechnicianRequest::with(['maintenanceCompany.user', 'service', 'category'])->findOrFail($id);
        return view('admin.technician_requests.show', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $techRequest = TechnicianRequest::findOrFail($id);
        $techRequest->update($request->all());
        return back()->with('success', __('Request updated successfully.'));
    }

    public function destroy($id)
    {
        $techRequest = TechnicianRequest::findOrFail($id);
        $techRequest->delete();
        return redirect()->route('admin.technician-requests.index')->with('success', __('Request deleted successfully.'));
    }

    public function accept(Request $request, $id)
    {
        $techRequest = TechnicianRequest::findOrFail($id);

        if ($techRequest->status !== 'pending') {
            return back()->with('error', __('Request already processed.'));
        }

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        // Check for existing user by email or phone (normalized)
        $normalizedPhone = $techRequest->phone;
        if ($normalizedPhone) {
            $normalizedPhone = preg_replace('/[^0-9]/', '', $normalizedPhone);
            if (str_starts_with($normalizedPhone, '966')) $normalizedPhone = substr($normalizedPhone, 3);
            if (str_starts_with($normalizedPhone, '0')) $normalizedPhone = substr($normalizedPhone, 1);
        }

        $existingUser = \App\Models\User::where('email', $techRequest->email)
            ->orWhere('phone', $normalizedPhone)
            ->first();

        if ($existingUser) {
            $errorMessage = $existingUser->email === $techRequest->email
                ? __('A user with this email already exists.')
                : __('A user with this phone number already exists.');
            return back()->with('error', $errorMessage);
        }

        // 1. Create User
        $user = \App\Models\User::create([
            'name' => $techRequest->name_ar ?? $techRequest->name,
            'email' => $techRequest->email,
            'phone' => $techRequest->phone,
            'password' => Hash::make($request->password),
            'type' => 'technician',
            'avatar' => $techRequest->photo,
        ]);

        // 2. Create Technician Profile
        \App\Models\Technician::create([
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

        // 3. Send Email
        try {
            Mail::to($techRequest->email)->send(new TechnicianAccountCreated($techRequest->email, $request->password));
        } catch (\Exception $e) {
             // Log error or handle silently for now
        }

        return redirect()->route('admin.technician-requests.index')->with([
            'success' => __('Request accepted successfully.'),
            'success_onboarding' => true
        ]);
    }

    public function refuse(Request $request, $id)
    {
        $techRequest = TechnicianRequest::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $techRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return redirect()->route('admin.technician-requests.index')->with('success', __('Request refused successfully.'));
    }
}
