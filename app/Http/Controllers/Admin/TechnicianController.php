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
        \App\Models\Order::syncExpiredOrders();

        $query = Technician::whereHas('user', function ($q) {
            $q->where('type', 'technician');
        })->with(['user', 'service', 'category', 'maintenanceCompany'])
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
            if ($request->tech_status === 'unavailable') {
                $query->where('availability_status', '!=', 'available');
            } else {
                $query->where('availability_status', $request->tech_status);
            }
        }

        // 6. Account Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status; 
            $query->whereHas('user', function ($q) use ($status) {
                if ($status === 'inactive' || $status === 'blocked') {
                    $q->whereIn('status', ['inactive', 'blocked']);
                } else {
                    $q->where('status', $status);
                }
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

        // Statistics for Index Page with growth compared to last week
        $totalTechs = Technician::count();
        $totalTechsLastWeek = Technician::where('created_at', '<', Carbon::now()->subWeek())->count();
        $totalTechsGrowth = $totalTechsLastWeek > 0 ? (($totalTechs - $totalTechsLastWeek) / $totalTechsLastWeek) * 100 : ($totalTechs > 0 ? 100 : 0);

        $activeTechs = Technician::whereHas('user', function($q) { $q->where('status', 'active'); })->count();
        $activeTechsLastWeek = Technician::whereHas('user', function($q) { $q->where('status', 'active'); })->where('created_at', '<', Carbon::now()->subWeek())->count();
        $activeTechsGrowth = $activeTechsLastWeek > 0 ? (($activeTechs - $activeTechsLastWeek) / $activeTechsLastWeek) * 100 : ($activeTechs > 0 ? 100 : 0);

        $completedOrders = Order::where('status', 'completed')->whereNotNull('technician_id')->count();
        $completedOrdersLastWeek = Order::where('status', 'completed')->whereNotNull('technician_id')->where('created_at', '<', Carbon::now()->subWeek())->count();
        $completedOrdersGrowth = $completedOrdersLastWeek > 0 ? (($completedOrders - $completedOrdersLastWeek) / $completedOrdersLastWeek) * 100 : ($completedOrders > 0 ? 100 : 0);

        $avgRating = Review::avg('rating') ?? 0;
        $avgRatingLastWeek = Review::where('created_at', '<', Carbon::now()->subWeek())->avg('rating') ?? 0;
        $avgRatingGrowth = $avgRatingLastWeek > 0 ? (($avgRating - $avgRatingLastWeek) / $avgRatingLastWeek) * 100 : ($avgRating > 0 ? 100 : 0);

        $stats = [
            'total_technicians' => $totalTechs,
            'total_technicians_trend' => ($totalTechsGrowth >= 0 ? '+' : '') . number_format($totalTechsGrowth, 2) . '%',
            'total_technicians_trend_value' => $totalTechsGrowth,
            'active_technicians' => $activeTechs,
            'active_technicians_trend' => ($activeTechsGrowth >= 0 ? '+' : '') . number_format($activeTechsGrowth, 2) . '%',
            'active_technicians_trend_value' => $activeTechsGrowth,
            'total_completed_orders' => $completedOrders,
            'total_completed_orders_trend' => ($completedOrdersGrowth >= 0 ? '+' : '') . number_format($completedOrdersGrowth, 2) . '%',
            'total_completed_orders_trend_value' => $completedOrdersGrowth,
            'average_rating' => $avgRating,
            'average_rating_trend' => ($avgRatingGrowth >= 0 ? '+' : '') . number_format($avgRatingGrowth, 2) . '%',
            'average_rating_trend_value' => $avgRatingGrowth,
        ];
        
        return view('admin.technicians.index', compact('items', 'stats', 'categories', 'services'));
    }

    public function top(Request $request)
    {
        // Get all top technicians sorted by completed orders and ratings
        $query = Technician::whereHas('user', function ($q) {
            $q->where('type', 'technician')
              ->where('status', 'active');
        })->with(['user', 'service', 'category', 'maintenanceCompany'])
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
            'phone' => 'required|string|regex:/^\+?[0-9\s\-]+$/|unique:users',
            'category_id' => 'required|exists:services,id',
            'service_id' => 'nullable|exists:services,id',
            'years_experience' => 'nullable|integer',
            'status' => 'required|string', 
            'image' => 'nullable|image|max:2048',
            'national_id_image' => 'nullable|image|max:2048',
            'national_id' => 'nullable|string|max:255',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'city_id' => 'required|exists:cities,id',
            'districts' => 'nullable|array',
            'districts.*' => 'exists:districts,id',
        ];

        $request->validate($rules, [
            'phone.regex' => __('Phone number must contain only numbers.'),
            'password.min' => __('The password is too short. It must be at least 8 characters.'),
        ]);

        $password = $request->password ?? Str::random(10);
        
        $user = User::create([
            'name' => $request->name_ar, // Use Arabic name as primary user name
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'technician',
            'phone' => $request->phone,
            'city_id' => $request->city_id,
            'status' => $request->status,
        ]);
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/technicians'), $imageName);
            $imagePath = 'uploads/technicians/' . $imageName;
        }

        $idImagePath = null;
        if ($request->hasFile('national_id_image')) {
            $idImageName = time() . '_' . uniqid() . '.' . $request->file('national_id_image')->getClientOriginalExtension();
            $request->file('national_id_image')->move(public_path('uploads/technicians/ids'), $idImageName);
            $idImagePath = 'uploads/technicians/ids/' . $idImageName;
        }

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

        // Send SMS credentials to the technician
        try {
            $taqnyat = new \App\Services\TaqnyatOTP();
            $message = "مرحباً {$user->name}، تم إنشاء حساب فني لك في تطبيق مطرقة بنجاح.\nبيانات الدخول:\nرقم الجوال: {$user->phone}\nكلمة المرور: {$password}";
            $taqnyat->sendSMS($user->phone, $message);
        } catch (\Exception $e) {
            \Log::error('[TechnicianController] Failed to send SMS: ' . $e->getMessage());
        }

        return redirect()->route('admin.technicians.index')->with('success', __('Technician created successfully.'));
    }

    public function show(Request $request, $id)
    {
        $item = Technician::with(['user', 'service', 'category', 'maintenanceCompany', 'user.city', 'schedules'])->findOrFail($id);
        
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

        if ($request->filled('customer_type') && $request->customer_type !== 'all') {
            $ordersQuery->whereHas('user', function($q) use ($request) {
                $q->where('type', $request->customer_type);
            });
        }

        if ($request->filled('date')) {
            $ordersQuery->whereDate('created_at', $request->date);
        }

        if ($request->get('sort_by') === 'oldest') {
            $ordersQuery->oldest();
        } else {
            $ordersQuery->latest();
        }
        
        $orders = $ordersQuery->get();

        // Fetch Financial Settlements
        $settlements = FinancialSettlement::where('user_id', $item->user_id)
            ->with(['order'])
            ->latest()
            ->get();

        $reviews = Review::where('technician_id', $item->id)->with(['order', 'user'])->latest()->get();

        // Performance Chart Data
        $chartType = $request->get('chart_type', 'weekly');
        $performanceData = [];
        $locale = app()->getLocale();
        
        if ($chartType === 'yearly') {
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $label = $date->locale($locale)->translatedFormat('M');
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        } elseif ($chartType === 'monthly') {
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::now()->subWeeks($i)->startOfWeek();
                $end = $start->copy()->endOfWeek();
                $weekNum = 4 - $i;
                $label = __('Week :number', ['number' => $weekNum]);
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        } else { // weekly
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $label = $date->locale($locale)->translatedFormat('l');
                $count = Order::where('technician_id', $item->id)
                    ->where('status', 'completed')
                    ->whereDate('created_at', $date->toDateString())
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        }

        if ($request->ajax()) {
            return response()->json(['performanceData' => $performanceData]);
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('admin.technicians.show', compact('item', 'stats', 'orders', 'reviews', 'performanceData', 'chartType', 'settlements', 'days'));
    }

    public function updateSchedules(Request $request, $id)
    {
        $technician = Technician::findOrFail($id);

        $request->validate([
            'schedules' => 'nullable|array',
        ]);

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        // Validate first
        foreach ($days as $day) {
            $input = $request->input("schedules.{$day}");
            if ($input && !empty($input['active'])) {
                if (empty($input['start_time']) || empty($input['end_time'])) {
                    return back()->withErrors(["schedules.{$day}.start_time" => __('Start and end times are required for active days.')])->withInput();
                }
                if ($input['end_time'] <= $input['start_time']) {
                    return back()->withErrors(["schedules.{$day}.end_time" => __('End time must be after start time.')])->withInput();
                }
            }
        }

        // Sync to database
        foreach ($days as $day) {
            $input = $request->input("schedules.{$day}");

            if ($input && !empty($input['active'])) {
                $technician->schedules()->updateOrCreate(
                    ['day' => $day],
                    [
                        'start_time' => $input['start_time'],
                        'end_time' => $input['end_time']
                    ]
                );
            } else {
                $technician->schedules()->where('day', $day)->delete();
            }
        }

        return redirect()->route('admin.technicians.show', $technician->id)->with('success', __('Working hours updated successfully.'));
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
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|regex:/^\+?[0-9\s\-]+$/|unique:users,phone,'.$technician->user_id,
            'image' => 'nullable|image|max:2048',
            'national_id_image' => 'nullable|image|max:2048',
            'category_id' => 'required|exists:services,id',
            'service_id' => 'nullable|exists:services,id',
            'years_experience' => 'nullable|integer',
            'status' => 'required|string',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'city_id' => 'required|exists:cities,id',
            'districts' => 'nullable|array',
            'districts.*' => 'exists:districts,id',
        ];

        $request->validate($rules, [
            'phone.regex' => __('Phone number must contain only numbers.'),
            'password.min' => __('The password is too short. It must be at least 8 characters.'),
        ]);
        
        $user = $technician->user;
        if ($user) {
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;
            if ($request->has('city_id')) $user->city_id = $request->city_id;
            $user->name = $request->name_ar;
            $user->save();
        }

        $data = $request->except(['email', 'password', 'phone', 'status', 'type', 'image', 'national_id_image', 'districts']);
        
        if ($request->hasFile('image')) {
            if ($technician->image && file_exists(public_path($technician->image))) {
                unlink(public_path($technician->image));
            }
            $imageName = time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads/technicians'), $imageName);
            $data['image'] = 'uploads/technicians/' . $imageName;
        }

        if ($request->hasFile('national_id_image')) {
            if ($technician->national_id_image && file_exists(public_path($technician->national_id_image))) {
                unlink(public_path($technician->national_id_image));
            }
            $idImageName = time() . '_' . uniqid() . '.' . $request->file('national_id_image')->getClientOriginalExtension();
            $request->file('national_id_image')->move(public_path('uploads/technicians/ids'), $idImageName);
            $data['national_id_image'] = 'uploads/technicians/ids/' . $idImageName;
        }

        $data['districts'] = $request->districts;

        $technician->update($data);

        return redirect()->route('admin.technicians.index')->with('success', __('Technician updated successfully.'));
    }

    public function destroy($id)
    {
        $technician = Technician::findOrFail($id);
        try {
            $user = $technician->user;
            $technician->delete();
            if ($user) {
                $user->delete();
            }
            $message = __('Technician deleted successfully.');
        } catch (\Exception $e) {
            if ($technician->user) {
                $technician->user->update(['status' => 'inactive']);
            }
            $message = __('Technician deleted successfully.');
        }
        return back()->with('success', $message);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => __('No technicians selected.')], 400);
        }

        $technicians = Technician::whereIn('id', $ids)->get();
        foreach ($technicians as $technician) {
            try {
                $user = $technician->user;
                $technician->delete();
                if ($user) {
                    $user->delete();
                }
            } catch (\Exception $e) {
                if ($technician->user) {
                    $technician->user->update(['status' => 'inactive']);
                }
            }
        }

        return response()->json(['success' => true, 'message' => __('Selected technicians deleted successfully.')]);
    }

    public function toggleBlock($id)
    {
        $technician = Technician::findOrFail($id);
        $user = $technician->user;
        
        if ($user) {
             if ($user->status === 'blocked' || $user->status === 'inactive') {
                $user->status = 'active';
                $user->blocked_at = null;
                $message = __('Technician activated successfully');
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
                if ($status === 'inactive') {
                    $q->whereIn('status', ['inactive', 'blocked']);
                } else {
                    $q->where('status', $status);
                }
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

    public function bulkBlock(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => __('No technicians selected.')]);
        }

        $technicians = Technician::whereIn('id', $ids)->with('user')->get();
        foreach ($technicians as $technician) {
            if ($technician->user && $technician->user->status !== 'blocked') {
                $technician->user->status = 'blocked';
                $technician->user->blocked_at = now();
                $technician->user->save();
            }
        }

        return response()->json(['success' => true, 'message' => __('Selected technicians have been blocked.')]);
    }

    public function bulkUnblock(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => __('No technicians selected.')]);
        }

        $technicians = Technician::whereIn('id', $ids)->with('user')->get();
        foreach ($technicians as $technician) {
            if ($technician->user && $technician->user->status === 'blocked') {
                $technician->user->status = 'active';
                $technician->user->blocked_at = null;
                $technician->user->save();
            }
        }

        return response()->json(['success' => true, 'message' => __('Selected technicians have been unblocked.')]);
    }
}
