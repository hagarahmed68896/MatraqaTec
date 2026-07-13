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
        Order::syncExpiredOrders();

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
                
                // Add Date Search Support (YYYY-MM-DD)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $search)) {
                    $q->orWhereDate('scheduled_at', $search);
                }
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
        
        $totalPremium = Order::whereIn('status', ['scheduled', 'in_progress', 'completed'])->count();
        $totalPremiumLastWeek = Order::whereIn('status', ['scheduled', 'in_progress', 'completed'])->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $totalPremiumGrowth = $totalPremiumLastWeek > 0 ? (($totalPremium - $totalPremiumLastWeek) / $totalPremiumLastWeek) * 100 : ($totalPremium > 0 ? 100 : 0);

        $scheduledPremium = Order::where('status', 'scheduled')->count();
        $scheduledPremiumLastWeek = Order::where('status', 'scheduled')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $scheduledPremiumGrowth = $scheduledPremiumLastWeek > 0 ? (($scheduledPremium - $scheduledPremiumLastWeek) / $scheduledPremiumLastWeek) * 100 : ($scheduledPremium > 0 ? 100 : 0);

        $inProgressPremium = Order::where('status', 'in_progress')->count();
        $inProgressPremiumLastWeek = Order::where('status', 'in_progress')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $inProgressPremiumGrowth = $inProgressPremiumLastWeek > 0 ? (($inProgressPremium - $inProgressPremiumLastWeek) / $inProgressPremiumLastWeek) * 100 : ($inProgressPremium > 0 ? 100 : 0);

        $completedPremium = Order::where('status', 'completed')->count();
        $completedPremiumLastWeek = Order::where('status', 'completed')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $completedPremiumGrowth = $completedPremiumLastWeek > 0 ? (($completedPremium - $completedPremiumLastWeek) / $completedPremiumLastWeek) * 100 : ($completedPremium > 0 ? 100 : 0);

        $stats = [
            'total' => $totalPremium,
            'total_trend' => ($totalPremiumGrowth >= 0 ? '+' : '') . number_format($totalPremiumGrowth, 2) . '%',
            'total_trend_value' => $totalPremiumGrowth,
            'scheduled' => $scheduledPremium,
            'scheduled_trend' => ($scheduledPremiumGrowth >= 0 ? '+' : '') . number_format($scheduledPremiumGrowth, 2) . '%',
            'scheduled_trend_value' => $scheduledPremiumGrowth,
            'in_progress' => $inProgressPremium,
            'in_progress_trend' => ($inProgressPremiumGrowth >= 0 ? '+' : '') . number_format($inProgressPremiumGrowth, 2) . '%',
            'in_progress_trend_value' => $inProgressPremiumGrowth,
            'completed' => $completedPremium,
            'completed_trend' => ($completedPremiumGrowth >= 0 ? '+' : '') . number_format($completedPremiumGrowth, 2) . '%',
            'completed_trend_value' => $completedPremiumGrowth,
        ];

        // Get categories and services for filters
        $categories = \App\Models\Service::whereNull('parent_id')->get();
        $services = \App\Models\Service::whereNotNull('parent_id')->get();

        return view('admin.orders.premium', compact('items', 'stats', 'categories', 'services'));
    }

    public function index(Request $request)
    {
        Order::syncExpiredOrders();

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
                
                // Add Date Search Support (YYYY-MM-DD)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $search)) {
                    $q->orWhereDate('scheduled_at', $search);
                }
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
        
        $totalOrders = Order::count();
        $totalOrdersLastWeek = Order::where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $totalOrdersGrowth = $totalOrdersLastWeek > 0 ? (($totalOrders - $totalOrdersLastWeek) / $totalOrdersLastWeek) * 100 : ($totalOrders > 0 ? 100 : 0);

        $scheduledOrders = Order::where('status', 'scheduled')->count();
        $scheduledOrdersLastWeek = Order::where('status', 'scheduled')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $scheduledOrdersGrowth = $scheduledOrdersLastWeek > 0 ? (($scheduledOrders - $scheduledOrdersLastWeek) / $scheduledOrdersLastWeek) * 100 : ($scheduledOrders > 0 ? 100 : 0);

        $inProgressOrders = Order::where('status', 'in_progress')->count();
        $inProgressOrdersLastWeek = Order::where('status', 'in_progress')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $inProgressOrdersGrowth = $inProgressOrdersLastWeek > 0 ? (($inProgressOrders - $inProgressOrdersLastWeek) / $inProgressOrdersLastWeek) * 100 : ($inProgressOrders > 0 ? 100 : 0);

        $completedOrders = Order::where('status', 'completed')->count();
        $completedOrdersLastWeek = Order::where('status', 'completed')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $completedOrdersGrowth = $completedOrdersLastWeek > 0 ? (($completedOrders - $completedOrdersLastWeek) / $completedOrdersLastWeek) * 100 : ($completedOrders > 0 ? 100 : 0);

        $rejectedOrders = Order::where('status', 'rejected')->count();
        $rejectedOrdersLastWeek = Order::where('status', 'rejected')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $rejectedOrdersGrowth = $rejectedOrdersLastWeek > 0 ? (($rejectedOrders - $rejectedOrdersLastWeek) / $rejectedOrdersLastWeek) * 100 : ($rejectedOrders > 0 ? 100 : 0);

        $newOrders = Order::where('status', 'new')->count();
        $newOrdersLastWeek = Order::where('status', 'new')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $newOrdersGrowth = $newOrdersLastWeek > 0 ? (($newOrders - $newOrdersLastWeek) / $newOrdersLastWeek) * 100 : ($newOrders > 0 ? 100 : 0);

        $stats = [
            'total' => $totalOrders,
            'total_trend' => ($totalOrdersGrowth >= 0 ? '+' : '') . number_format($totalOrdersGrowth, 2) . '%',
            'total_trend_value' => $totalOrdersGrowth,
            'scheduled' => $scheduledOrders,
            'scheduled_trend' => ($scheduledOrdersGrowth >= 0 ? '+' : '') . number_format($scheduledOrdersGrowth, 2) . '%',
            'scheduled_trend_value' => $scheduledOrdersGrowth,
            'in_progress' => $inProgressOrders,
            'in_progress_trend' => ($inProgressOrdersGrowth >= 0 ? '+' : '') . number_format($inProgressOrdersGrowth, 2) . '%',
            'in_progress_trend_value' => $inProgressOrdersGrowth,
            'completed' => $completedOrders,
            'completed_trend' => ($completedOrdersGrowth >= 0 ? '+' : '') . number_format($completedOrdersGrowth, 2) . '%',
            'completed_trend_value' => $completedOrdersGrowth,
            'rejected' => $rejectedOrders,
            'rejected_trend' => ($rejectedOrdersGrowth >= 0 ? '+' : '') . number_format($rejectedOrdersGrowth, 2) . '%',
            'rejected_trend_value' => $rejectedOrdersGrowth,
            'new' => $newOrders,
            'new_trend' => ($newOrdersGrowth >= 0 ? '+' : '') . number_format($newOrdersGrowth, 2) . '%',
            'new_trend_value' => $newOrdersGrowth,
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
        $user = User::find($request->user_id);
        $validated['city_id'] = $request->city_id ?? ($user->city_id ?? null);
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

        // Send notifications if newly assigned
        if ($request->filled('technician_id') && ($request->technician_id != $item->technician_id)) {
            $validated['assigned_at'] = now();
            $tech = \App\Models\Technician::find($request->technician_id);
            if ($tech && $tech->user) {
                $duration = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);
                $this->sendNotification($tech->user_id, [
                    'type' => \App\Models\Notification::TYPE_NEW_ORDER,
                    'title_ar' => 'مهمة جديدة',
                    'title_en' => 'New Task Assigned',
                    'body_ar' => "تم تعيين مهمة جديدة لك، يرجى القبول أو الرفض خلال $duration دقيقة",
                    'body_en' => "You have been assigned a new task. Please accept or reject within $duration minutes",
                    'data' => ['order_id' => $item->id]
                ]);
            }
        } elseif ($request->filled('maintenance_company_id') && ($request->maintenance_company_id != $item->maintenance_company_id)) {
            $validated['assigned_at'] = now();
            $company = \App\Models\MaintenanceCompany::find($request->maintenance_company_id);
            if ($company && $company->user) {
                $this->sendNotification($company->user_id, [
                    'type' => \App\Models\Notification::TYPE_NEW_ORDER,
                    'title_ar' => 'طلب صيانة جديد',
                    'title_en' => 'New Maintenance Request',
                    'body_ar' => 'تم تعيين طلب جديد لشركتكم، يرجى مراجعته وتعيين فني',
                    'body_en' => 'A new request has been assigned to your company. Please review and assign a technician',
                    'data' => ['order_id' => $item->id]
                ]);
            }
        }

        $item->update($validated);
        return redirect()->route('admin.orders.index')->with('success', __('Order updated successfully.'));
    }

    public function accept(Request $request, $id)
    {
        // Mark notification as read if provided
        if ($request->has('notification_id')) {
            \App\Models\Notification::where('id', $request->notification_id)
                ->where('user_id', auth()->id())
                ->update(['is_read' => true]);
        }

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
        
        // If assigned to an independent technician, it's scheduled.
        // If assigned to a company or a company-owned technician, it remains 'new' so it appears in their New tab for confirmation.
        $status = 'new';
        if ($request->technician_id) {
            $tech = \App\Models\Technician::find($request->technician_id);
            if ($tech && !$tech->maintenance_company_id) {
                $status = 'scheduled';
            }
        }
        $order->status = $status;
        $order->assigned_at = now(); // Reset timer on assignment
        if ($request->scheduled_at) {
            $order->scheduled_at = $request->scheduled_at;
        }
        
        $order->save();

        // Send notifications
        $duration = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);
        if ($order->technician_id) {
            $tech = \App\Models\Technician::find($order->technician_id);
            // Only notify technician here if they are independent (don't belong to a company)
            // Because if they belong to a company, the company must accept first.
            if ($tech && $tech->user && !$tech->maintenance_company_id) {
                $this->sendNotification($tech->user_id, [
                    'type' => \App\Models\Notification::TYPE_NEW_ORDER,
                    'title_ar' => 'مهمة جديدة',
                    'title_en' => 'New Task Assigned',
                    'body_ar' => "تم تعيين مهمة جديدة لك، يرجى القبول أو الرفض خلال $duration دقيقة",
                    'body_en' => "You have been assigned a new task. Please accept or reject within $duration minutes",
                    'data' => ['order_id' => $order->id]
                ]);
            }
        }

        if ($order->maintenance_company_id) {
            $company = \App\Models\MaintenanceCompany::find($order->maintenance_company_id);
            if ($company && $company->user) {
                $this->sendNotification($company->user_id, [
                    'type' => \App\Models\Notification::TYPE_NEW_ORDER,
                    'title_ar' => 'طلب صيانة جديد',
                    'title_en' => 'New Maintenance Request',
                    'body_ar' => 'تم تعيين طلب جديد لشركتكم، يرجى مراجعته وتعيين فني',
                    'body_en' => 'A new request has been assigned to your company. Please review and assign a technician',
                    'data' => ['order_id' => $order->id]
                ]);
            }
        }

        return redirect()->back()->with('success', __('Order accepted and assigned successfully.'));
    }

    public function refuse(Request $request, $id)
    {
        // Mark notification as read if provided
        if ($request->has('notification_id')) {
            \App\Models\Notification::where('id', $request->notification_id)
                ->where('user_id', auth()->id())
                ->update(['is_read' => true]);
        }

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
        
        $service = $order->service;
        $categoryId = $service?->parent_id ?? $service?->id;
        $cityId = $order->city_id ?? ($order->user->city_id ?? null);

        $query = Technician::with(['user', 'service'])
            ->whereHas('user', function($q) use ($cityId) {
                // If order has a city, show techs in that city OR techs with no city specified (all cities)
                $q->when($cityId, function($sub) use ($cityId) {
                    $sub->where(function($cityQuery) use ($cityId) {
                        $cityQuery->where('city_id', $cityId)
                                 ->orWhereNull('city_id');
                    });
                })
                ->where('status', 'active');
                // ->where('is_online', true); // Removed online requirement for Admin Dashboard
        })
        // ->where('availability_status', 'available') // Removed availability requirement for Admin Dashboard
            ->where(function($q) use ($order, $categoryId) {
                // Show techs with matching service OR no service specified (generalists)
                $q->where('service_id', $order->service_id)
                  ->orWhere('category_id', $categoryId)
                  ->orWhereNull('service_id');
            });

        // Availability check (only if order has a scheduled time)
        if ($order->scheduled_at) {
            $start = $order->scheduled_at->copy()->subHours(1)->addMinute();
            $end = $order->scheduled_at->copy()->addHours(1)->subMinute();
            
            $query->whereDoesntHave('orders', function($q) use ($start, $end) {
                $q->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                  ->whereBetween('scheduled_at', [$start, $end]);
            })
            ->whereDoesntHave('appointments', function($q) use ($start, $end) {
                $q->whereIn('status', ['scheduled', 'in_progress'])
                  ->whereBetween('appointment_date', [$start, $end]);
            });
        }

        $technicians = $query->withAvg('reviews', 'rating')
            ->withCount('orders')
            ->orderByRaw("CASE WHEN technicians.service_id = ? THEN 0 ELSE 1 END", [$order->service_id])
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
                    'avatar' => $tech->user->avatar_url,
                    'specialty' => __('Specialist in maintenance services'),
                    'service_name' => $tech->service->name_ar ?? __('Technician'),
                    'rating' => round($tech->reviews_avg_rating ?? 0, 1),
                    'district' => is_array($tech->districts) && count($tech->districts) > 0 
                                    ? implode(', ', array_column($tech->districts, 'name')) 
                                    : ($tech->districts ?? __('All Regions')),
                    'order_count' => $tech->orders_count ?? 5,
                    'lat' => $lat,
                    'lng' => $lng,
                    'status_label' => __('Available'),
                ];
            })
        ]);
    }

    public function getAvailableCompanies(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $categoryId = $order->service?->parent_id ?? $order->service_id;

        $cityId = $order->city_id ?? ($order->user->city_id ?? null);
        \Log::info("OrderAssignment - Companies - Order: {$id}, CityId: " . ($cityId ?? 'null') . ", Category: {$categoryId}");

        $companies = MaintenanceCompany::with(['user', 'city'])
            ->whereHas('user', function($q) {
                $q->where('status', 'active');
            })
            ->when($cityId, function($q) use ($cityId) {
                return $q->where('city_id', $cityId);
            })
            ->whereHas('services', function($q) use ($order, $categoryId) {
                $q->where('services.id', $order->service_id)
                  ->orWhere('services.id', $categoryId);
            })
            ->get();
        \Log::info("OrderAssignment - Companies - Results Count: " . $companies->count());

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
                    'avatar' => $comp->user->avatar_url,
                    'specialty' => __('Certified Maintenance Company'),
                    'rating' => 4.5,
                    'district' => $comp->city->name_ar ?? __('Riyadh District'),
                    'lat' => $lat,
                    'lng' => $lng,
                    'status_label' => __('Available'),
                ];
            })
        ]);
    }

    private function sendNotification($userId, $details)
    {
        $user = \App\Models\User::find($userId);
        if ($user) {
            $targetAudience = match ($user->type) {
                'technician' => 'technicians',
                'maintenance_company' => 'companies',
                'individual', 'corporate_customer' => 'clients',
                default => 'individual_user',
            };

            \App\Models\Notification::create([
                'user_id' => $userId,
                'type' => $details['type'] ?? 'notification',
                'target_audience' => $targetAudience,
                'title_ar' => $details['title_ar'],
                'title_en' => $details['title_en'],
                'body_ar' => $details['body_ar'],
                'body_en' => $details['body_en'],
                'data' => $details['data'] ?? [],
                'status' => 'sent',
                'is_read' => false
            ]);

            $user->sendFcmNotification($details, false);
        }
    }
}
