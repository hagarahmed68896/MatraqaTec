<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCompany;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MaintenanceCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceCompany::with(['user', 'technicians.service', 'orders', 'financialSettlements']);

        // 1. Search Logic
        if ($request->has('search') && $request->search) {
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

        // 2. Filter by Status
        if ($request->has('status') && $request->status) {
            $status = $request->status; 
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // 3. Sorting Logic
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

        $items = $query->paginate(10);
        
        // Extract unique services
        $items->getCollection()->transform(function ($company) {
            $company->services = $company->technicians->pluck('service')->unique('id')->values();
            return $company;
        });

        return view('admin.maintenance_companies.index', compact('items'));
    }

    public function create()
    {
        return view('admin.maintenance_companies.create');
    }

    public function store(Request $request)
    {
        $locale = app()->getLocale();
        $validator = Validator::make($request->all(), [
            'company_name_en' => 'required|string|max:255',
            'company_name_ar' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|unique:users',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
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

        MaintenanceCompany::create([
            'user_id' => $user->id,
            'company_name_en' => $request->company_name_en,
            'company_name_ar' => $request->company_name_ar,
            'commercial_record_number' => $request->commercial_record_number,
            'tax_number' => $request->tax_number,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.maintenance-companies.index')->with('success', __('Company created successfully.'));
    }

    public function show(Request $request, $id)
    {
        $company = MaintenanceCompany::with(['user', 'technicians.service', 'orders.service', 'financialSettlements', 'user.city'])
            ->where('user_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();
        
        $technicians = $company->technicians;
        $orders = Order::where('maintenance_company_id', $company->id)->with(['service', 'user', 'technician'])->latest()->get();
        $settlements = $company->financialSettlements()->latest()->get();
        
        $company->services = $technicians->pluck('service')->unique('id')->values();

        // Statistics Summary
        $stats = [
            'total_technicians' => $technicians->count(),
            'total_services' => $technicians->pluck('service_id')->unique()->count(),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->where('status', 'completed')->sum('total_price'),
        ];

        // Performance Chart Data (Revenue)
        $chartType = $request->get('chart_type', 'monthly');
        $performanceData = [];
        
        if ($chartType === 'weekly') {
            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subWeeks($i);
                $label = __('Week') . ' ' . $date->weekOfYear;
                $sum = Order::where('maintenance_company_id', $company->id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$date->startOfWeek()->toDateTimeString(), $date->endOfWeek()->toDateTimeString()])
                    ->sum('total_price');
                $performanceData[] = ['label' => $label, 'count' => $sum];
            }
        } elseif ($chartType === 'yearly') {
            for ($i = 2; $i >= 0; $i--) {
                $date = Carbon::now()->subYears($i);
                $label = $date->year;
                $sum = Order::where('maintenance_company_id', $company->id)
                    ->where('status', 'completed')
                    ->whereYear('created_at', $date->year)
                    ->sum('total_price');
                $performanceData[] = ['label' => $label, 'count' => $sum];
            }
        } else { // monthly
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $label = $date->translatedFormat('F');
                $sum = Order::where('maintenance_company_id', $company->id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total_price');
                $performanceData[] = ['label' => $label, 'count' => $sum];
            }
        }

        if ($request->ajax()) {
            return response()->json(['performanceData' => $performanceData]);
        }
        
        return view('admin.maintenance_companies.show', compact('company', 'stats', 'technicians', 'orders', 'settlements', 'performanceData', 'chartType'));
    }

    public function edit($id)
    {
        $company = MaintenanceCompany::with('user')->findOrFail($id);
        return view('admin.maintenance_companies.edit', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $company = MaintenanceCompany::findOrFail($id);
        
        $user = $company->user;
        if ($user) {
            $user->email = $request->email;
            if ($request->filled('password')) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('company_name_en')) $user->name = $request->company_name_en;
            $user->save();
        }

        $company->update($request->except(['email', 'password', 'phone', 'status', 'type']));

        return redirect()->route('admin.maintenance-companies.index')->with('success', __('Company updated successfully.'));
    }

    public function destroy($id)
    {
        $company = MaintenanceCompany::findOrFail($id);
        if ($company->user) {
            $company->user->delete();
        } else {
            $company->delete();
        }
        return redirect()->route('admin.maintenance-companies.index')->with('success', __('Company deleted successfully.'));
    }

    public function toggleBlock($id)
    {
        $company = MaintenanceCompany::findOrFail($id);
        $user = $company->user;
        
        if ($user) {
             if ($user->status === 'blocked') {
                $user->status = 'active';
                $user->blocked_at = null;
                $message = __('Company unblocked successfully');
            } else {
                $user->status = 'blocked';
                $user->blocked_at = now();
                $message = __('Company blocked successfully');
            }
            $user->save();
             return back()->with('success', $message);
        }

        return back()->with('error', __('User record not found'));
    }
}
