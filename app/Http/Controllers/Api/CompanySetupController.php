<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanySetupController extends Controller
{
    /**
     * list services ALREADY selected by the company (with counts)
     */
    public function myServices(Request $request)
    {
        $user = auth()->user();
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $services = $company->services()
            ->select('services.id', 'services.name_ar', 'services.name_en', 'services.image', 'services.icon')
            ->get()
            ->map(function ($service) use ($company) {
                // Count sub-services (children of this service)
                $service->sub_service_count = Service::where('parent_id', $service->id)->count();
                
                // Count technicians belonging to this company that are assigned to this service (or child services)
                $service->technician_count = DB::table('technicians')
                    ->where('maintenance_company_id', $company->id)
                    ->where(function($query) use ($service) {
                        $query->where('category_id', $service->id)
                              ->orWhere('service_id', $service->id);
                    })
                    ->count();

                return $service;
            });

        return response()->json(['status' => true, 'message' => 'Your services retrieved', 'data' => $services]);
    }

    /**
     * list ALL services with selected status (for selection grid)
     */
    public function listServices(Request $request)
    {
        $user = auth()->user();
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $selectedServiceIds = $company->services()->pluck('services.id')->toArray();

        // Get parent services for selection grid as seen in screenshot
        $services = Service::whereNull('parent_id')
            ->select('id', 'name_ar', 'name_en', 'icon')
            ->get()
            ->map(function ($service) use ($selectedServiceIds) {
                $service->is_selected = in_array($service->id, $selectedServiceIds);
                return $service;
            });

        return response()->json(['status' => true, 'message' => 'All services retrieved', 'data' => $services]);
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
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $company->services()->sync($request->service_ids);

        return response()->json(['status' => true, 'message' => 'Services updated successfully']);
    }

    /**
     * remove a specific service from company
     */
    public function removeService($id)
    {
        $user = auth()->user();
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $company->services()->detach($id);

        return response()->json(['status' => true, 'message' => 'Service removed successfully']);
    }

    /**
     * list coverage areas (districts) grouped by city?? 
     */
    public function listCoverageAreas(Request $request)
    {
        $user = auth()->user();
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $selectedDistrictIds = $company->districts()->pluck('districts.id')->toArray();

        // Filter by specific city if requested, otherwise use company's main city
        $cityId = $request->input('city_id', $company->city_id);

        $query = District::query();
        if ($cityId) {
            $query->where('city_id', $cityId);
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
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $company->districts()->sync($request->district_ids);

        return response()->json(['status' => true, 'message' => 'Coverage areas updated successfully']);
    }
}
