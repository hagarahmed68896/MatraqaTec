<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\User;
use App\Models\Service;
use App\Models\Order; // Added for statistics
use App\Models\Review; // Added for statistics
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\FinancialSettlement;
use App\Models\City;
use App\Models\District;
use Carbon\Carbon;

class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $query = Technician::with(['user', 'service', 'category', 'maintenanceCompany'])
            ->withCount(['orders' => function($q) {
                $q->where('status', 'completed');
            }]);

        // 1. Tech Type Filter (Tabs)
        $type = $request->get('type', 'platform'); // 'platform' or 'company'
        if ($type === 'company') {
            $query->whereNotNull('maintenance_company_id');
        } else {
            $query->whereNull('maintenance_company_id');
        }

        // 2. Search Logic
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
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

        // 5. Tech Status (Availability) Filter
        if ($request->filled('tech_status')) {
            $query->where('availability_status', $request->tech_status);
        }

        // 6. Account Status Filter
        if ($request->filled('status')) {
            $status = $request->status; 
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // 7. Sorting Logic
        $sort = $request->get('sort_by', 'newest');
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

        // Statistics for Index Page
        $stats = [
            'total_technicians' => Technician::count(),
            'active_technicians' => Technician::whereHas('user', function($q) { $q->where('status', 'active'); })->count(),
            'total_completed_orders' => Order::where('status', 'completed')->whereNotNull('technician_id')->count(),
            'average_rating' => Review::avg('rating') ?? 0,
        ];
        
        return view('admin.technicians.index', compact('items', 'stats', 'categories', 'services'));
    }

    public function top(Request $request)
    {
        // Get all top technicians sorted by completed orders and ratings
        $query = Technician::with(['user', 'service', 'category', 'maintenanceCompany'])
            ->withCount(['orders' => function($q) {
                $q->where('status', 'completed');
            }])
            ->withAvg('reviews', 'rating');

        // Search Logic
        if ($request->has('search') && $request->search) {
            $searchTerms = explode(' ', trim($request->search));
            
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($subQ) use ($term) {
                        $subQ->where('name_en', 'like', "%{$term}%")
                             ->orWhere('name_ar', 'like', "%{$term}%")
                             ->orWhereHas('user', function ($q2) use ($term) {
                                  $q2->where('email', 'like', "%{$term}%")
                                     ->orWhere('phone', 'like', "%{$term}%")
                                     ->orWhere('name', 'like', "%{$term}%");
                              })
                             ->orWhereHas('category', function ($q3) use ($term) {
                                  $q3->where('name_en', 'like', "%{$term}%")
                                     ->orWhere('name_ar', 'like', "%{$term}%");
                              })
                             ->orWhereHas('service', function ($q4) use ($term) {
                                  $q4->where('name_en', 'like', "%{$term}%")
                                     ->orWhere('name_ar', 'like', "%{$term}%");
                              });
                    });
                }
            });
        }

        $items = $query->orderByDesc('orders_count')->paginate(20);
        
        return view('admin.technicians.top', compact('items'));
    }

    public function create()
    {
        $services = Service::whereNull('parent_id')->with('children')->get();
        $cities = City::with('districts')->get();
        return view('admin.technicians.create', compact('services', 'cities'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'required|string|unique:users',
            'category_id' => 'required|exists:services,id',
            'service_id' => 'nullable|exists:services,id',
            'years_experience' => 'nullable|integer',
            'status' => 'required|string', 
            'image' => 'nullable|image|max:2048',
            'national_id_image' => 'nullable|image|max:2048',
            'national_id' => 'nullable|string|max:255',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'districts' => 'nullable|array',
            'districts.*' => 'exists:districts,id',
        ];

        $request->validate($rules);

        $password = $request->password ?? Str::random(10);
        
        $user = User::create([
            'name' => $request->name_ar, // Use Arabic name as primary user name
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'technician',
            'phone' => $request->phone,
            'status' => $request->status,
        ]);
        
        $imagePath = $request->hasFile('image') ? $request->file('image')->store('technicians', 'public') : null;
        $idImagePath = $request->hasFile('national_id_image') ? $request->file('national_id_image')->store('technicians/ids', 'public') : null;

        Technician::create([
            'user_id' => $user->id,
            'name' => $request->name_ar,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'email' => $request->email,
            'phone' => $request->phone,
            'category_id' => $request->category_id,
            'service_id' => $request->service_id,
            'national_id' => $request->national_id, 
            'national_id_image' => $idImagePath,
            'bio_en' => $request->bio_en,
            'bio_ar' => $request->bio_ar,
            'years_experience' => $request->years_experience ?? 0,
            'image' => $imagePath,
            'districts' => $request->districts,
            'availability_status' => 'available',
        ]);

        return redirect()->route('admin.technicians.index')->with('success', __('Technician created successfully.'));
    }

    public function show(Request $request, $id)
    {
        $item = Technician::with(['user', 'service', 'category', 'maintenanceCompany', 'user.city'])->findOrFail($id);
        
        // Detailed Stats
        $stats = [
             'total_orders' => Order::where('technician_id', $item->id)->count(),
             'completed_orders' => Order::where('technician_id', $item->id)->where('status', 'completed')->count(),
             'revenue' => Order::where('technician_id', $item->id)->where('status', 'completed')->sum('total_price'),
             'rating' => Review::where('technician_id', $item->id)->avg('rating') ?? 0,
        ];

        // Fetch Orders for Tasks Tab
        $ordersQuery = Order::where('technician_id', $item->id)
            ->with(['service', 'service.parent', 'user', 'technician', 'technician.maintenanceCompany']);
        
        if ($request->filled('order_status') && $request->order_status !== 'all') {
            $ordersQuery->where('status', $request->order_status);
        }
        
        $orders = $ordersQuery->latest()->get();

        // Fetch Financial Settlements
        $settlements = FinancialSettlement::where('user_id', $item->user_id)
            ->with(['order'])
            ->latest()
            ->get();

        $reviews = Review::where('technician_id', $item->id)->with(['order', 'user'])->latest()->get();

        // Performance Chart Data
        $chartType = $request->get('chart_type', 'monthly');
        $performanceData = [];
        
        if ($chartType === 'weekly') {
            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subWeeks($i);
                $label = __('Week') . ' ' . $date->weekOfYear;
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$date->startOfWeek()->toDateTimeString(), $date->endOfWeek()->toDateTimeString()])
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        } elseif ($chartType === 'yearly') {
            for ($i = 2; $i >= 0; $i--) {
                $date = Carbon::now()->subYears($i);
                $label = $date->year;
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereYear('created_at', $date->year)
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        } else { // monthly
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $label = $date->translatedFormat('F');
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        }

        if ($request->ajax()) {
            return response()->json(['performanceData' => $performanceData]);
        }

        return view('admin.technicians.show', compact('item', 'stats', 'orders', 'reviews', 'performanceData', 'chartType', 'settlements'));
    }

    public function edit($id)
    {
        $item = Technician::findOrFail($id);
        $services = Service::whereNull('parent_id')->with('children')->get();
        $cities = City::with('districts')->get();
        return view('admin.technicians.edit', compact('item', 'services', 'cities'));
    }

    public function update(Request $request, $id)
    {
        $technician = Technician::findOrFail($id);
        
        $rules = [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$technician->user_id,
            'phone' => 'nullable|string|unique:users,phone,'.$technician->user_id,
            'image' => 'nullable|image|max:2048',
            'national_id_image' => 'nullable|image|max:2048',
            'category_id' => 'required|exists:services,id',
            'service_id' => 'nullable|exists:services,id',
            'years_experience' => 'nullable|integer',
            'status' => 'required|string',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'districts' => 'nullable|array',
            'districts.*' => 'exists:districts,id',
        ];

        $request->validate($rules);
        
        $user = $technician->user;
        if ($user) {
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;
            $user->name = $request->name_ar;
            $user->save();
        }

        $data = $request->except(['email', 'password', 'phone', 'status', 'type', 'image', 'national_id_image', 'districts']);
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('technicians', 'public');
        }

        if ($request->hasFile('national_id_image')) {
            $data['national_id_image'] = $request->file('national_id_image')->store('technicians/ids', 'public');
        }

        $data['districts'] = $request->districts;

        $technician->update($data);

        return redirect()->route('admin.technicians.index')->with('success', __('Technician updated successfully.'));
    }

    public function destroy($id)
    {
        $technician = Technician::findOrFail($id);
        if ($technician->user) {
            $technician->user->delete();
        } else {
            $technician->delete();
        }
        return redirect()->route('admin.technicians.index')->with('success', __('Technician deleted successfully.'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => __('No technicians selected.')], 400);
        }

        $technicians = Technician::whereIn('id', $ids)->get();
        foreach ($technicians as $technician) {
            if ($technician->user) {
                $technician->user->delete();
            } else {
                $technician->delete();
            }
        }

        return response()->json(['success' => true, 'message' => __('Selected technicians deleted successfully.')]);
    }

    public function toggleBlock($id)
    {
        $technician = Technician::findOrFail($id);
        $user = $technician->user;
        
        if ($user) {
             if ($user->status === 'blocked') {
                $user->status = 'active';
                $user->blocked_at = null;
                $message = __('Technician unblocked successfully');
            } else {
                $user->status = 'blocked';
                $user->blocked_at = now();
                $message = __('Technician blocked successfully');
            }
            $user->save();
             return back()->with('success', $message);
        }

        return back()->with('error', __('User record not found'));
    }

    public function download(Request $request)
    {
        $query = Technician::with(['user', 'service', 'category']);

        // 1. Search Logic
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
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

        $items = $query->latest()->get();

        $csvFileName = 'technicians_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function () use ($items) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Row
            fputcsv($handle, [
                __('ID'),
                __('Name (AR)'),
                __('Name (EN)'),
                __('Email'),
                __('Phone'),
                __('Service'),
                __('Category'),
                __('Type'),
                __('Years Experience'),
                __('Rating'),
                __('Status'),
                __('Created At'),
            ]);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->name_ar,
                    $item->name_en,
                    $item->user->email ?? '',
                    $item->user->phone ?? '',
                    $item->service->name_ar ?? '',
                    $item->category->name_ar ?? '',
                    $item->maintenanceCompany ? __('Corporate') : __('Independent'),
                    $item->years_experience,
                    number_format($item->reviews()->avg('rating') ?? 0, 1),
                    __($item->user->status ?? 'active'),
                    $item->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
