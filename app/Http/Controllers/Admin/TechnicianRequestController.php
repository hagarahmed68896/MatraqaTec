<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\TechnicianAccountCreated;

class TechnicianRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicianRequest::with(['maintenanceCompany.user', 'service', 'category']);

        // 1. Status Filter (Tabs)
        $status = $request->get('status', 'pending');
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
        \Log::info('TechnicianRequestController@accept called', ['id' => $id, 'request' => $request->all()]);

        // Mark notification as read if provided
        if ($request->has('notification_id')) {
            \App\Models\Notification::where('id', $request->notification_id)
                ->where('user_id', auth()->id())
                ->update(['is_read' => true]);
        }

        $techRequest = TechnicianRequest::findOrFail($id);

        if ($techRequest->status !== 'pending') {
            return back()->with('error', __('Request already processed.'));
        }

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        // 2. Acceptance Logic
        try {
            DB::beginTransaction();

            // Robust phone normalization for comparison
            $normalizedPhone = preg_replace('/[^0-9]/', '', $techRequest->phone);
            if (str_starts_with($normalizedPhone, '966')) $normalizedPhone = substr($normalizedPhone, 3);
            if (str_starts_with($normalizedPhone, '0')) $normalizedPhone = substr($normalizedPhone, 1);

            // Detailed search for existing user (support both formats in DB)
            $existingUser = \App\Models\User::where('email', $techRequest->email)
                ->orWhere('phone', $normalizedPhone)
                ->orWhere('phone', '0' . $normalizedPhone)
                ->first();

            if ($existingUser) {
                $errorMessage = $existingUser->email === $techRequest->email
                    ? __('This email is already registered in the system.')
                    : __('This phone number is already registered in the system.');
                DB::rollBack();
                return back()->with('error', $errorMessage);
            }

            // Create new user
            $user = \App\Models\User::create([
                'name' => $techRequest->name_ar ?? $techRequest->name,
                'email' => $techRequest->email,
                'phone' => $techRequest->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'type' => 'technician',
                'avatar' => $techRequest->photo,
                'status' => 'active',
            ]);

            // 3. Create Technician Profile
            \App\Models\Technician::create([
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

            // 4. Update Request Status
            $techRequest->update(['status' => 'accepted']);

            DB::commit();

            // 5. Send Email (Optional)
            try {
                \Log::info('TechnicianRequestController: Sending email', ['email' => $techRequest->email]);
            Mail::to($techRequest->email)->send(new TechnicianAccountCreated($user, $request->password));
            \Log::info('TechnicianRequestController: Email sent successfully');
            } catch (\Exception $e) {
                // Ignore email errors
            }

            return redirect()->route('admin.technician-requests.index')->with([
                'success' => __('Request accepted successfully.'),
                'success_onboarding' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('An error occurred while accepting the request: ') . $e->getMessage());
        }
    }

    public function refuse(Request $request, $id)
    {
        // Mark notification as read if provided
        if ($request->has('notification_id')) {
            \App\Models\Notification::where('id', $request->notification_id)
                ->where('user_id', auth()->id())
                ->update(['is_read' => true]);
        }

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
