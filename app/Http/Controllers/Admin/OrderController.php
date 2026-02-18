<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Service;
use App\Models\Technician;
use App\Models\MaintenanceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use \App\Traits\HasAutoAssignment;

    public function premium(Request $request)
    {
        $query = Order::with(['user', 'technician.user', 'maintenanceCompany.user', 'service.parent'])
            ->whereIn('status', ['scheduled', 'in_progress', 'completed']);

        // 1. Filter by status via tabs
        $currentTab = $request->get('tab', 'scheduled');
        if (in_array($currentTab, ['scheduled', 'in_progress', 'completed'])) {
            $query->where('status', $currentTab);
        }

        // 1b. Filter by specific sub_status
        if ($request->filled('sub_status')) {
            $query->where('sub_status', $request->sub_status);
        }

        // 2. Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // 3. Filter by Customer Type
        if ($request->filled('customer_type')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('type', $request->customer_type);
            });
        }

        // 4. Filter by Technician Type
        if ($request->filled('technician_type')) {
            if ($request->technician_type === 'platform') {
                $query->whereNotNull('technician_id')->whereNull('maintenance_company_id');
            } elseif ($request->technician_type === 'company') {
                $query->whereNotNull('maintenance_company_id');
            }
        }

        // 5. Filter by Appointment Status
        if ($request->filled('appointment_status')) {
            if ($request->appointment_status === 'assigned') {
                $query->where(function ($q) {
                    $q->whereNotNull('technician_id')->orWhereNotNull('maintenance_company_id');
                });
            } elseif ($request->appointment_status === 'waiting') {
                $query->whereNull('technician_id')->whereNull('maintenance_company_id');
            }
        }

        // 6. Filter by Service Category
        if ($request->filled('service_category_id')) {
            $query->whereHas('service', function ($q) use ($request) {
                $q->where('parent_id', $request->service_category_id);
            });
        }

        // 7. Filter by Service IDs (child services)
        if ($request->filled('service_ids')) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereIn('service_id', $serviceIds);
        }

        // 8. Sorting
        $sortBy = $request->get('sort_by', 'newest');
        switch ($sortBy) {
            case 'name':
                $query->join('users', 'orders.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'asc')
                      ->select('orders.*');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Handle Download
        if ($request->filled('download')) {
            $orders = $query->get();
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\OrdersExport($orders),
                'premium_orders_' . date('Y-m-d_H-i-s') . '.xlsx'
            );
        }

        $items = $query->paginate(25);
        
        $stats = [
            'total' => Order::whereIn('status', ['scheduled', 'in_progress', 'completed'])->count(),
            'scheduled' => Order::where('status', 'scheduled')->count(),
            'in_progress' => Order::where('status', 'in_progress')->count(),
            'completed' => Order::where('status', 'completed')->count(),
        ];

        // Get categories and services for filters
        $categories = \App\Models\Service::whereNull('parent_id')->get();
        $services = \App\Models\Service::whereNotNull('parent_id')->get();

        return view('admin.orders.premium', compact('items', 'stats', 'categories', 'services'));
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'technician.user', 'maintenanceCompany.user', 'service.parent']);

        // 1. Filter by tab/status
        $currentTab = $request->get('tab', 'new');
        if (in_array($currentTab, ['new', 'scheduled', 'in_progress', 'completed', 'rejected'])) {
            $query->where('status', $currentTab);
        }

        // 1b. Filter by specific sub_status
        if ($request->filled('sub_status')) {
            $query->where('sub_status', $request->sub_status);
        }

        // 2. Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // 3. Filter by Customer Type
        if ($request->filled('customer_type')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('type', $request->customer_type);
            });
        }

        // 4. Filter by Service Category (Parent)
        if ($request->filled('service_category_id')) {
            $query->whereHas('service', function ($q) use ($request) {
                $q->where('parent_id', $request->service_category_id);
            });
        }

        // 5. Filter by Key Service IDs (Child Services) - Multi-select support
        if ($request->filled('service_ids')) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereIn('service_id', $serviceIds);
        }

        // 6. Filter by Technician Type
        if ($request->filled('technician_type')) {
            if ($request->technician_type === 'platform') {
                $query->whereNotNull('technician_id')->whereNull('maintenance_company_id');
            } elseif ($request->technician_type === 'company') {
                $query->whereNotNull('maintenance_company_id');
            }
        }

        // 6b. Filter by Appointment Status (Assigned vs Waiting)
        if ($request->filled('appointment_status')) {
            if ($request->appointment_status === 'assigned') {
                $query->where(function ($q) {
                    $q->whereNotNull('technician_id')->orWhereNotNull('maintenance_company_id');
                });
            } elseif ($request->appointment_status === 'waiting') {
                $query->whereNull('technician_id')->whereNull('maintenance_company_id');
            }
        }

        // 7. Filter by User or Technician ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        // 8. Sorting
        $sortBy = $request->get('sort_by', 'newest');
        switch ($sortBy) {
            case 'name':
                $query->join('users', 'orders.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'asc')
                      ->select('orders.*');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $items = $query->paginate(25);
        
        $stats = [
            'total' => Order::count(),
            'scheduled' => Order::where('status', 'scheduled')->count(),
            'in_progress' => Order::where('status', 'in_progress')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'rejected' => Order::where('status', 'rejected')->count(),
            'new' => Order::where('status', 'new')->count(),
        ];

        $categories = Service::whereNull('parent_id')->with('children')->get();

        return view('admin.orders.index', compact('items', 'stats', 'categories'));
    }

    public function create()
    {
        $users = User::all();
        $services = Service::whereNotNull('parent_id')->get();
        $technicians = Technician::with('user')->get();
        return view('admin.orders.create', compact('users', 'services', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'status' => 'required|string',
            'total_price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $lastOrder = Order::latest('id')->first();
        $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
        $validated['order_number'] = $nextId;

        Order::create($validated);
        return redirect()->route('admin.orders.index')->with('success', __('Order created successfully.'));
    }

    public function show($id)
    {
        $item = Order::with(['user', 'technician.user', 'technician.category', 'maintenanceCompany.user', 'service.parent', 'attachments', 'reviews', 'payments', 'appointments'])->findOrFail($id);
        $technicians = Technician::with('user')->get();
        $companies = MaintenanceCompany::with('user')->get();
        return view('admin.orders.show', compact('item', 'technicians', 'companies'));
    }

    public function edit($id)
    {
        $item = Order::findOrFail($id);
        $users = User::all();
        $services = Service::whereNotNull('parent_id')->get();
        $technicians = Technician::with('user')->get();
        return view('admin.orders.edit', compact('item', 'users', 'services', 'technicians'));
    }

    public function update(Request $request, $id)
    {
        $item = Order::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|string',
            'total_price' => 'nullable|numeric',
            'technician_id' => 'nullable|exists:technicians,id',
            'maintenance_company_id' => 'nullable|exists:maintenance_companies,id',
        ]);

        $item->update($validated);
        return redirect()->route('admin.orders.index')->with('success', __('Order updated successfully.'));
    }

    public function accept(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'technician_id' => 'nullable|exists:technicians,id',
            'maintenance_company_id' => 'nullable|exists:maintenance_companies,id',
            'scheduled_at' => 'nullable|date',
        ]);

        if (!$request->technician_id && !$request->maintenance_company_id) {
            return back()->withErrors(['technician_id' => __('Please select a technician or a maintenance company')]);
        }

        $order->technician_id = $request->technician_id;
        $order->maintenance_company_id = $request->maintenance_company_id;
        $order->status = 'scheduled';
        if ($request->scheduled_at) {
            $order->scheduled_at = $request->scheduled_at;
        }
        
        $order->save();

        return redirect()->back()->with('success', __('Order accepted and assigned successfully.'));
    }

    public function refuse(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $order->status = 'rejected';
        $order->rejection_reason = $request->rejection_reason;
        $order->save();

        return redirect()->back()->with('success', __('Order refused successfully.'));
    }

    public function destroy($id)
    {
        $item = Order::findOrFail($id);
        $item->delete();
        return redirect()->back()->with('success', __('Order deleted successfully.'));
    }
    public function getAvailableTechnicians(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $start = (clone $order->scheduled_at)->subHours(1)->addMinute();
        $end = (clone $order->scheduled_at)->addHours(1)->subMinute();
        
        $service = $order->service;
        $categoryId = $service->parent_id ?? $service->id;

        $technicians = Technician::with(['user', 'service'])
            ->whereHas('user', function($q) use ($order) {
                $q->where('city_id', $order->city_id)
                  ->where('status', 'active');
            })
            ->where(function($q) use ($order, $categoryId) {
                $q->where('service_id', $order->service_id)
                  ->orWhere('category_id', $categoryId);
            })
            ->whereDoesntHave('orders', function($q) use ($start, $end) {
                $q->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                  ->whereBetween('scheduled_at', [$start, $end]);
            })
            ->whereDoesntHave('appointments', function($q) use ($start, $end) {
                $q->whereIn('status', ['scheduled', 'in_progress'])
                  ->whereBetween('appointment_date', [$start, $end]);
            })
            ->withAvg('reviews', 'rating')
            ->withCount('orders')
            ->orderByDesc('reviews_avg_rating')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $technicians->map(function($tech, $index) {
                // For demo purposes, we randomize positions slightly if coordinates are missing or identical
                $lat = $tech->user->latitude ?? 24.7136;
                $lng = $tech->user->longitude ?? 46.6753;
                
                if ($lat == 24.7136 && $lng == 46.6753) {
                    $lat += (rand(-100, 100) / 100); // Varied across the region
                    $lng += (rand(-100, 100) / 100);
                }

                return [
                    'id' => $tech->id,
                    'name' => $tech->user->name,
                    'avatar' => $tech->user->avatar ? asset('storage/' . $tech->user->avatar) : null,
                    'specialty' => $tech->bio_ar ?? 'متخصص في خدمات الصيانة',
                    'service_name' => $tech->service->name_ar ?? 'فني',
                    'rating' => round($tech->reviews_avg_rating ?? 0, 1),
                    'district' => is_array($tech->districts) ? implode(', ', $tech->districts) : ($tech->districts ?? 'جميع المناطق'),
                    'order_count' => $tech->orders_count ?? 5,
                    'lat' => $lat,
                    'lng' => $lng,
                    'status_label' => 'متاح',
                ];
            })
        ]);
    }

    public function getAvailableCompanies(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $categoryId = $order->service->parent_id ?? $order->service_id;

        $companies = MaintenanceCompany::with(['user', 'city'])
            ->where('city_id', $order->city_id)
            ->whereHas('services', function($q) use ($order, $categoryId) {
                $q->where('services.id', $order->service_id)
                  ->orWhere('services.id', $categoryId);
            })
            ->get();

        return response()->json([
            'status' => true,
            'data' => $companies->map(function($comp, $index) {
                // For demo purposes, we randomize positions slightly if coordinates are missing or identical
                $lat = $comp->user->latitude ?? 24.7136;
                $lng = $comp->user->longitude ?? 46.6753;
                
                if ($lat == 24.7136 && $lng == 46.6753) {
                    $lat += (rand(-100, 100) / 100);
                    $lng += (rand(-100, 100) / 100);
                }

                return [
                    'id' => $comp->id,
                    'name' => $comp->company_name_ar ?? $comp->user->name,
                    'avatar' => $comp->user->avatar ? asset('storage/' . $comp->user->avatar) : null,
                    'specialty' => 'شركة صيانة معتمدة',
                    'rating' => 4.5,
                    'district' => $comp->city->name_ar ?? 'حي الرياض',
                    'lat' => $lat,
                    'lng' => $lng,
                    'status_label' => 'متاح',
                ];
            })
        ]);
    }
}
