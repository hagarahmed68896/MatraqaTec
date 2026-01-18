<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Service;
use Illuminate\Http\Request;

class CompanySetupController extends Controller
{
    /**
     * list services with selected status
     */
    public function listServices(Request $request)
    {
        $user = auth()->user();
        if (!$user->maintenanceCompany) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $company = $user->maintenanceCompany;
        $selectedServiceIds = $company->services()->pluck('services.id')->toArray();

        // Get all leaf services (services that don't have children) or just all services?
        // Usually providers select specific services. Assuming all services for now.
        $services = Service::select('id', 'name_ar', 'name_en', 'icon')
            ->get()
            ->map(function ($service) use ($selectedServiceIds) {
                $service->is_selected = in_array($service->id, $selectedServiceIds);
                return $service;
            });

        return response()->json(['status' => true, 'message' => 'Services retrieved', 'data' => $services]);
    }

    /**
     * update selected services
     */
    public function updateServices(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $user = auth()->user();
        if (!$user->maintenanceCompany) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $user->maintenanceCompany->services()->sync($request->service_ids);

        return response()->json(['status' => true, 'message' => 'Services updated successfully']);
    }

    /**
     * list coverage areas (districts) grouped by city?? 
     * Or simply list districts of a selected city. 
     * For now, listing all districts in the company's city if company has city_id, or all.
     */
    public function listCoverageAreas(Request $request)
    {
        $user = auth()->user();
        if (!$user->maintenanceCompany) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $company = $user->maintenanceCompany;
        $selectedDistrictIds = $company->districts()->pluck('districts.id')->toArray();

        // If company has a city_id, show districts for that city? Or allow multi-city?
        // "Select Coverage Area" usually implies districts within a city.
        $query = District::query();
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $districts = $query->select('id', 'name_ar', 'name_en', 'city_id')
            ->with('city:id,name_ar,name_en')
            ->get()
            ->map(function ($district) use ($selectedDistrictIds) {
                $district->is_selected = in_array($district->id, $selectedDistrictIds);
                return $district;
            });

        return response()->json(['status' => true, 'message' => 'Coverage areas retrieved', 'data' => $districts]);
    }

    /**
     * update selected coverage areas
     */
    public function updateCoverageAreas(Request $request)
    {
        $request->validate([
            'district_ids' => 'required|array',
            'district_ids.*' => 'exists:districts,id',
        ]);

        $user = auth()->user();
        if (!$user->maintenanceCompany) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $user->maintenanceCompany->districts()->sync($request->district_ids);

        return response()->json(['status' => true, 'message' => 'Coverage areas updated successfully']);
    }
}
