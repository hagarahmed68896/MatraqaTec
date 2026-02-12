<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicianRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TechnicianRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicianRequest::with(['maintenanceCompany.user', 'service', 'category'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(15);
        return view('admin.technician_requests.index', compact('items'));
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

        return redirect()->route('admin.technician-requests.index')->with('success', __('Request accepted and technician profile created.'));
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
