<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Order;

class MaintenanceCompanyController extends Controller
{
public function index(Request $request)
{
    $query = MaintenanceCompany::with(['user', 'technicians.service', 'orders', 'financialSettlements']);

    // 1. Search Logic (Existing)
    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('company_name_en', 'like', "%{$search}%")
              ->orWhere('company_name_ar', 'like', "%{$search}%")
              ->orWhereHas('user', function ($q2) use ($search) {
                  $q2->where('email', 'like', "%{$search}%")
                     ->orWhere('phone', 'like', "%{$search}%")
                     ->orWhere('name', 'like', "%{$search}%");
              });
        });
    }

    // 2. NEW: Filter by Status (Active/Inactive)
    if ($request->has('status')) {
        $status = $request->status; 
        $query->whereHas('user', function ($q) use ($status) {
            $q->where('status', $status);
        });
    }

    // 3. Sorting Logic (Existing)
    if ($request->has('sort_by')) {
        switch ($request->sort_by) {
            case 'name':
                $query->orderBy('company_name_en', 'asc');
                break;
            case 'status':
                $query->join('users', 'maintenance_companies.user_id', '=', 'users.id')
                      ->orderBy('users.status', 'asc')
                      ->select('maintenance_companies.*');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
    } else {
        $query->orderBy('created_at', 'desc');
    }

    $companies = $query->paginate(10);
    
    // Extract unique services
    $companies->getCollection()->transform(function ($company) {
        $company->services = $company->technicians->pluck('service')->unique('id')->values();
        return $company;
    });
    
    return response()->json(['status' => true, 'message' => 'Companies retrieved', 'data' => $companies]);
}
    public function blockedIndex()
    {
        $companies = MaintenanceCompany::whereHas('user', function ($query) {
            $query->where('status', 'blocked');
        })->with(['user', 'technicians.service', 'orders', 'financialSettlements'])->orderBy('created_at', 'desc')->paginate(10);
        
        // Extract unique services for each company
        $companies->getCollection()->transform(function ($company) {
            $company->services = $company->technicians->pluck('service')->unique('id')->values();
            return $company;
        });
        
        return response()->json(['status' => true, 'message' => 'Blocked companies retrieved', 'data' => $companies]);
    }

    public function store(Request $request)
    {
        $locale = app()->getLocale();
        $validator = Validator::make($request->all(), [
            'company_name_en' => $locale == 'en' ? 'required|string|max:255' : 'nullable|string|max:255',
            'company_name_ar' => $locale == 'ar' ? 'required|string|max:255' : 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $password = $request->password ?? Str::random(10);
        $name = $request->company_name_en ?? $request->company_name_ar;

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'maintenance_company',
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $company = MaintenanceCompany::create([
            'user_id' => $user->id,
            'company_name_en' => $request->company_name_en,
            'company_name_ar' => $request->company_name_ar,
            'commercial_record_number' => $request->commercial_record_number,
            'tax_number' => $request->tax_number,
            'address' => $request->address,
        ]);
        
        $company->load('user');

        return response()->json(['status' => true, 'message' => 'Company created successfully. Password: ' . $password, 'data' => $company]);
    }

    public function show($id)
    {
        $company = MaintenanceCompany::with(['user', 'technicians.service', 'orders', 'financialSettlements'])->where('user_id', $id)->orWhere('id', $id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        
        $company->services = $company->technicians->pluck('service')->unique('id')->values();

        // 1. Statistics Summary
        $userId = $company->user_id;
        $totalTechnicians = $company->technicians()->count();
        $totalServices = $company->technicians->pluck('service_id')->unique()->count();
        $totalOrders = Order::where('maintenance_company_id', $company->id)->count();
        $totalRevenue = Order::where('maintenance_company_id', $company->id)
            ->where('status', 'completed')
            ->sum('total_price');

        // 2. Performance Trend (Last 30 days)
        $now = \Carbon\Carbon::now();
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'value' => Order::where('maintenance_company_id', $company->id)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count()
            ];
        }
        
        return response()->json([
            'status' => true, 
            'message' => 'Company retrieved', 
            'data' => [
                'profile' => $company,
                'statistics' => [
                    'total_technicians' => $totalTechnicians,
                    'total_services' => $totalServices,
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'performance_chart' => $chartData
                ]
            ]
        ]);
    }

    public function statistics($id)
    {
        return $this->show($id);
    }

    public function update(Request $request, $id)
    {
        $company = MaintenanceCompany::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        
        $user = $company->user;
        if ($user) {
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;

            // Sync user name if names are updated
            if ($request->has('company_name_en') || $request->has('company_name_ar')) {
                $user->name = $request->company_name_en ?? $request->company_name_ar ?? $company->company_name_en ?? $company->company_name_ar;
            }

            $user->save();
        }

        $company->update($request->except(['email', 'password', 'phone', 'status', 'type']));
        
        return response()->json(['status' => true, 'message' => 'Company updated', 'data' => $company->load('user')]);
    }

    public function destroy($id)
    {
        $company = MaintenanceCompany::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        if ($company->user) {
            $company->user->delete();
        } else {
            $company->delete();
        }

        return response()->json(['status' => true, 'message' => 'Company deleted successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids; 
        if (!is_array($ids)) {
             return response()->json(['status' => false, 'message' => 'IDs must be an array'], 422);
        }
        
        $count = 0;
        foreach($ids as $id) {
             $company = MaintenanceCompany::where('id', $id)->orWhere('user_id', $id)->first();
             if ($company) {
                 if ($company->user) $company->user->delete();
                 else $company->delete();
                 $count++;
             }
        }

        return response()->json(['status' => true, 'message' => "$count Companies deleted successfully"]);
    }
    
    public function download()
    {
        $companies = MaintenanceCompany::with('user')->get();
        return $this->generateCsv($companies, "maintenance_companies.csv");
    }

    public function downloadBlocked()
    {
        $companies = MaintenanceCompany::whereHas('user', function ($query) {
            $query->where('status', 'blocked');
        })->with('user')->get();
        
        return $this->generateCsv($companies, "blocked_maintenance_companies.csv");
    }

    private function generateCsv($companies, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name (AR)', 'Name (EN)', 'Email', 'Phone']); 

        foreach ($companies as $comp) {
            fputcsv($handle, [
                $comp->id,
                $comp->company_name_ar,
                $comp->company_name_en,
                $comp->user ? $comp->user->email : '',
                $comp->user ? $comp->user->phone : '',
            ]);
        }

        fseek($handle, 0);
        
        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
