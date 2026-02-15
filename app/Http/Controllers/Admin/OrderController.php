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

    public function index(Request $request)
    {
        $query = Order::with(['user', 'technician.user', 'maintenanceCompany.user', 'service.parent']);

        // 1. Filter by tab/status
        if ($request->has('tab')) {
            switch ($request->tab) {
                case 'new': $query->where('status', 'new'); break;
                case 'scheduled': $query->where('status', 'scheduled'); break;
                case 'in_progress': $query->where('status', 'in_progress'); break;
                case 'completed': $query->where('status', 'completed'); break;
                case 'rejected': $query->where('status', 'rejected'); break;
            }
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // 2. Search Logic
        if ($request->has('search') && $request->search) {
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
        if ($request->has('customer_type') && $request->customer_type) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('type', $request->customer_type);
            });
        }

        // 4. Filter by Service Category (Parent)
        if ($request->has('service_category_id') && $request->service_category_id) {
            $query->whereHas('service', function ($q) use ($request) {
                $q->where('parent_id', $request->service_category_id);
            });
        }

        // 5. Filter by Key Service IDs (Child Services) - Sync from API
        if ($request->has('service_ids') && $request->service_ids) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereIn('service_id', $serviceIds);
        }

        // 6. Filter by Technician Type - Sync from API
        if ($request->has('technician_type') && $request->technician_type) {
            if ($request->technician_type === 'platform') {
                $query->whereNotNull('technician_id')->whereNull('maintenance_company_id');
            } elseif ($request->technician_type === 'company') {
                $query->whereNotNull('maintenance_company_id');
            }
        }

        // 7. Filter by User or Technician ID
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('technician_id') && $request->technician_id) {
            $query->where('technician_id', $request->technician_id);
        }

        // 8. Sorting
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
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
        } else {
            $query->orderBy('created_at', 'desc');
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

        return view('admin.orders.index', compact('items', 'stats'));
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

        Order::create($validated);
        return redirect()->route('admin.orders.index')->with('success', __('Order created successfully.'));
    }

    public function show($id)
    {
        $item = Order::with(['user', 'technician.user', 'maintenanceCompany.user', 'service', 'attachments', 'reviews', 'payments', 'appointments'])->findOrFail($id);
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
