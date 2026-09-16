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

        $query = Order::with(['user', 'technician.user', 'maintenanceCompany.user', 'service.parent']);

        // 1. Filter by status via tabs
        $currentTab = $request->get('tab', 'all');
        if ($currentTab === 'scheduled') {
            $query->whereIn('status', ['scheduled', 'accepted']);
        } elseif ($currentTab === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($currentTab === 'completed') {
            $query->where('status', 'completed');
        } elseif ($currentTab === 'expired') {
            $query->where('status', 'expired');
        } elseif ($currentTab === 'cancelled') {
            $query->whereIn('status', ['cancelled', 'rejected']);
        }

        // 1b. Filter by specific sub_status
        if ($request->filled('sub_status')) {
            $query->where('sub_status', $request->sub_status);
        }

        // Day Filter (Today vs Future Reservations)
        if ($request->filled('day_filter')) {
            if ($request->day_filter === 'today') {
                $query->whereDate('scheduled_at', \Carbon\Carbon::today());
            } elseif ($request->day_filter === 'future') {
                $query->whereDate('scheduled_at', '>', \Carbon\Carbon::today());
            }
        }

        // 2. Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('order_number', 'like', "%{$search}%")
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
        
        $totalOrders = Order::count();
        $totalOrdersLastWeek = Order::where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $totalGrowth = $totalOrdersLastWeek > 0 ? (($totalOrders - $totalOrdersLastWeek) / $totalOrdersLastWeek) * 100 : ($totalOrders > 0 ? 100 : 0);

        $scheduledOrders = Order::whereIn('status', ['scheduled', 'accepted'])->count();
        $scheduledLastWeek = Order::whereIn('status', ['scheduled', 'accepted'])->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $scheduledGrowth = $scheduledLastWeek > 0 ? (($scheduledOrders - $scheduledLastWeek) / $scheduledLastWeek) * 100 : ($scheduledOrders > 0 ? 100 : 0);

        $inProgressOrders = Order::where('status', 'in_progress')->count();
        $inProgressLastWeek = Order::where('status', 'in_progress')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $inProgressGrowth = $inProgressLastWeek > 0 ? (($inProgressOrders - $inProgressLastWeek) / $inProgressLastWeek) * 100 : ($inProgressOrders > 0 ? 100 : 0);

        $completedOrders = Order::where('status', 'completed')->count();
        $completedLastWeek = Order::where('status', 'completed')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $completedGrowth = $completedLastWeek > 0 ? (($completedOrders - $completedLastWeek) / $completedLastWeek) * 100 : ($completedOrders > 0 ? 100 : 0);

        $expiredOrders = Order::where('status', 'expired')->count();
        $expiredLastWeek = Order::where('status', 'expired')->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $expiredGrowth = $expiredLastWeek > 0 ? (($expiredOrders - $expiredLastWeek) / $expiredLastWeek) * 100 : ($expiredOrders > 0 ? 100 : 0);

        $cancelledOrders = Order::whereIn('status', ['cancelled', 'rejected'])->count();
        $cancelledLastWeek = Order::whereIn('status', ['cancelled', 'rejected'])->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $cancelledGrowth = $cancelledLastWeek > 0 ? (($cancelledOrders - $cancelledLastWeek) / $cancelledLastWeek) * 100 : ($cancelledOrders > 0 ? 100 : 0);

        $stats = [
            'total' => $totalOrders,
            'total_trend' => ($totalGrowth >= 0 ? '+' : '') . number_format($totalGrowth, 2) . '%',
            'total_trend_value' => $totalGrowth,
            'scheduled' => $scheduledOrders,
            'scheduled_trend' => ($scheduledGrowth >= 0 ? '+' : '') . number_format($scheduledGrowth, 2) . '%',
            'scheduled_trend_value' => $scheduledGrowth,
            'in_progress' => $inProgressOrders,
            'in_progress_trend' => ($inProgressGrowth >= 0 ? '+' : '') . number_format($inProgressGrowth, 2) . '%',
            'in_progress_trend_value' => $inProgressGrowth,
            'completed' => $completedOrders,
            'completed_trend' => ($completedGrowth >= 0 ? '+' : '') . number_format($completedGrowth, 2) . '%',
            'completed_trend_value' => $completedGrowth,
            'expired' => $expiredOrders,
            'expired_trend' => ($expiredGrowth >= 0 ? '+' : '') . number_format($expiredGrowth, 2) . '%',
            'expired_trend_value' => $expiredGrowth,
            'cancelled' => $cancelledOrders,
            'cancelled_trend' => ($cancelledGrowth >= 0 ? '+' : '') . number_format($cancelledGrowth, 2) . '%',
            'cancelled_trend_value' => $cancelledGrowth,
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
        if ($currentTab === 'new') {
            $query->where('status', 'new')
                  ->whereNull('technician_id')
                  ->whereNull('maintenance_company_id');
        } elseif ($currentTab === 'scheduled') {
            $query->where(function($q) {
                $q->whereIn('status', ['scheduled', 'accepted'])
                  ->orWhere(function($sub) {
                      $sub->where('status', 'new')
                          ->where(function($sub2) {
                              $sub2->whereNotNull('technician_id')
                                   ->orWhereNotNull('maintenance_company_id');
                          });
                  });
            });
        } elseif (in_array($currentTab, ['in_progress', 'completed', 'rejected'])) {
            $query->where('status', $currentTab);
        }

        // 1b. Filter by specific sub_status
        if ($request->filled('sub_status')) {
            $query->where('sub_status', $request->sub_status);
        }

        // Day Filter (Today vs Future Reservations)
        if ($request->filled('day_filter')) {
            if ($request->day_filter === 'today') {
                $query->whereDate('scheduled_at', \Carbon\Carbon::today());
            } elseif ($request->day_filter === 'future') {
                $query->whereDate('scheduled_at', '>', \Carbon\Carbon::today());
            }
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

        $scheduledOrders = Order::where(function($q) {
            $q->whereIn('status', ['scheduled', 'accepted'])
              ->orWhere(function($sub) {
                  $sub->where('status', 'new')
                      ->where(function($sub2) {
                          $sub2->whereNotNull('technician_id')
                               ->orWhereNotNull('maintenance_company_id');
                      });
              });
        })->count();
        $scheduledOrdersLastWeek = Order::where(function($q) {
            $q->whereIn('status', ['scheduled', 'accepted'])
              ->orWhere(function($sub) {
                  $sub->where('status', 'new')
                      ->where(function($sub2) {
                          $sub2->whereNotNull('technician_id')
                               ->orWhereNotNull('maintenance_company_id');
                      });
              });
        })->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
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

        $newOrders = Order::where('status', 'new')
            ->whereNull('technician_id')
            ->whereNull('maintenance_company_id')
            ->count();
        $newOrdersLastWeek = Order::where('status', 'new')
            ->whereNull('technician_id')
            ->whereNull('maintenance_company_id')
            ->where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $newOrdersGrowth = $newOrdersLastWeek > 0 ? (($newOrders - $newOrdersLastWeek) / $newOrdersLastWeek) * 100 : ($newOrders > 0 ? 100 : 0);

        $managementOrdersTotal = $newOrders + $scheduledOrders + $rejectedOrders;
        $managementOrdersTotalLastWeek = $newOrdersLastWeek + $scheduledOrdersLastWeek + $rejectedOrdersLastWeek;
        $managementOrdersTotalGrowth = $managementOrdersTotalLastWeek > 0 ? (($managementOrdersTotal - $managementOrdersTotalLastWeek) / $managementOrdersTotalLastWeek) * 100 : ($managementOrdersTotal > 0 ? 100 : 0);

        $stats = [
            'total' => $managementOrdersTotal,
            'total_trend' => ($managementOrdersTotalGrowth >= 0 ? '+' : '') . number_format($managementOrdersTotalGrowth, 2) . '%',
            'total_trend_value' => $managementOrdersTotalGrowth,
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
        $services = Service::with('parent')->get();
        $technicians = Technician::with('user')->get();
        $companies = MaintenanceCompany::with('user')->get();
        return view('admin.orders.create', compact('users', 'services', 'technicians', 'companies'));
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
        Order::syncExpiredOrders();
        
        $item = Order::with(['user', 'technician.user', 'technician.category', 'maintenanceCompany.user', 'service.parent', 'attachments', 'reviews', 'payments', 'appointments'])->findOrFail($id);
        $technicians = Technician::with('user')->get();
        $companies = MaintenanceCompany::with('user')->get();
        return view('admin.orders.show', compact('item', 'technicians', 'companies'));
    }

    public function edit($id)
    {
        $item = Order::findOrFail($id);
        $users = User::all();
        $services = Service::with('parent')->get();
        $technicians = Technician::with('user')->get();
        $companies = MaintenanceCompany::with('user')->get();
        return view('admin.orders.edit', compact('item', 'users', 'services', 'technicians', 'companies'));
    }

    public function update(Request $request, $id)
    {
        $item = Order::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'status' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'technician_id' => 'nullable|exists:technicians,id',
            'maintenance_company_id' => 'nullable|exists:maintenance_companies,id',
        ]);

        $basePrice = (float) ($request->base_price ?? 0);
        $discount = (float) ($request->discount ?? 0);
        $totalPrice = ($basePrice - $discount) * 1.15;
        if ($totalPrice < 0) $totalPrice = 0;

        $validated['total_price'] = $totalPrice;
        $validated['discount'] = $discount;

        // Check if date changed to send notification
        $dateChanged = false;
        if ($request->filled('scheduled_at') && \Carbon\Carbon::parse($request->scheduled_at)->ne(\Carbon\Carbon::parse($item->scheduled_at))) {
            $dateChanged = true;
            if (!$item->original_scheduled_at && $item->scheduled_at) {
                $validated['original_scheduled_at'] = $item->scheduled_at;
            }
        }

        // Check if status changed to cancelled
        $statusChangedToCancelled = false;
        if ($validated['status'] === 'cancelled' && $item->status !== 'cancelled') {
            $statusChangedToCancelled = true;
        }

        // Restore status if it was expired and scheduled in the future
        if ($item->status === 'expired' && $request->filled('scheduled_at') && \Carbon\Carbon::parse($request->scheduled_at)->gt(now())) {
            $validated['status'] = $request->technician_id ? 'scheduled' : 'new';
            $validated['sub_status'] = null;
        }

        // If assigned to an independent technician, it's scheduled.
        // If assigned to a company, it remains 'new' so it appears in their New tab.
        if ($request->filled('technician_id') && ($request->technician_id != $item->technician_id)) {
            $validated['assigned_at'] = now();
            $tech = \App\Models\Technician::find($request->technician_id);
            if ($tech && !$tech->maintenance_company_id) {
                $validated['status'] = 'scheduled';
            }
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

        // Check if discount changed to send notification
        $discountChanged = false;
        $oldDiscount = (float) ($item->discount ?? 0);
        if ($discount > 0 && $discount != $oldDiscount) {
            $discountChanged = true;
        }

        $item->update($validated);

        // Send discount notification to user
        if ($discountChanged && $item->user_id) {
            $formattedDiscount = number_format($discount, 2);
            $this->sendNotification($item->user_id, [
                'type' => \App\Models\Notification::TYPE_STATUS_UPDATE,
                'title_ar' => 'تمت إضافة خصم لطلبك',
                'title_en' => 'Discount added to your order',
                'body_ar' => "تمت إضافة خصم بقيمة {$formattedDiscount} ر.س على طلبك رقم #{$item->order_number}.",
                'body_en' => "A discount of {$formattedDiscount} SAR has been added to your order #{$item->order_number}.",
                'data' => ['order_id' => $item->id]
            ]);
        }

        // Send reschedule notification to user
        if ($dateChanged && $item->user_id) {
            $formattedTime = \Carbon\Carbon::parse($item->scheduled_at)->format('Y-m-d g:i A');
            $this->sendNotification($item->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED,
                'title_ar' => 'إعادة جدولة الطلب',
                'title_en' => 'Order Rescheduled',
                'body_ar' => "تم تغيير موعد طلبك رقم #{$item->order_number} إلى {$formattedTime}",
                'body_en' => "Your order #{$item->order_number} has been rescheduled to {$formattedTime}",
                'data' => ['order_id' => $item->id]
            ]);
        }

        // Send reschedule notification to technician
        if ($dateChanged && $item->technician_id && $item->technician && $item->technician->user_id) {
            $formattedTime = \Carbon\Carbon::parse($item->scheduled_at)->format('Y-m-d g:i A');
            $this->sendNotification($item->technician->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED,
                'title_ar' => 'إعادة جدولة المهمة',
                'title_en' => 'Task Rescheduled',
                'body_ar' => "تم تغيير موعد المهمة رقم #{$item->order_number} إلى {$formattedTime}",
                'body_en' => "Task #{$item->order_number} has been rescheduled to {$formattedTime}",
                'data' => ['order_id' => $item->id]
            ]);
        }

        // Send cancellation notification to user & technician and refund to wallet
        if ($statusChangedToCancelled) {
            // Auto refund any completed payments to user wallet
            $totalPaid = (float) $item->payments()->where('status', 'completed')->sum('amount');
            if ($totalPaid > 0 && $item->user) {
                $user = $item->user;
                $user->wallet_balance = ($user->wallet_balance ?? 0) + $totalPaid;
                $user->save();

                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $totalPaid,
                    'type' => 'refund',
                    'note' => 'استرداد قيمة الطلب الملغي رقم #' . $item->order_number . ' إلى المحفظة',
                    'reference_id' => $item->id,
                    'reference_type' => Order::class,
                ]);

                $item->payments()->where('status', 'completed')->update(['status' => 'refunded']);
            }

            if ($item->user_id) {
                $refundMsgAr = ($totalPaid > 0) ? " وتم استرداد مبلغ {$totalPaid} ريال إلى محفظتك بنجاح." : "";
                $refundMsgEn = ($totalPaid > 0) ? " and {$totalPaid} SAR has been refunded to your wallet." : "";
                $this->sendNotification($item->user_id, [
                    'type' => \App\Models\Notification::TYPE_ORDER_CANCELLED,
                    'title_ar' => 'تم إلغاء الطلب',
                    'title_en' => 'Order Cancelled',
                    'body_ar' => "تم إلغاء طلبك رقم #{$item->order_number} من قبل الإدارة" . $refundMsgAr,
                    'body_en' => "Your order #{$item->order_number} has been cancelled by administration" . $refundMsgEn,
                    'data' => [
                        'order_id' => $item->id,
                        'refunded' => $totalPaid > 0,
                        'refund_amount' => $totalPaid,
                    ]
                ]);
            }
            if ($item->technician_id && $item->technician && $item->technician->user_id) {
                $this->sendNotification($item->technician->user_id, [
                    'type' => \App\Models\Notification::TYPE_ORDER_CANCELLED,
                    'title_ar' => 'تم إلغاء المهمة',
                    'title_en' => 'Task Cancelled',
                    'body_ar' => "تم إلغاء المهمة رقم #{$item->order_number} من قبل الإدارة",
                    'body_en' => "Task #{$item->order_number} has been cancelled by administration",
                    'data' => ['order_id' => $item->id]
                ]);
            }
        }

        // Release technician availability if order is no longer in progress
        if ($item->technician && in_array($item->status, ['completed', 'cancelled', 'rejected', 'expired'])) {
            $item->technician->update(['availability_status' => 'available']);
        }

        // Sync Appointment
        if ($item->scheduled_at) {
            \App\Models\Appointment::updateOrCreate(
                ['order_id' => $item->id],
                [
                    'technician_id' => $item->technician_id,
                    'appointment_date' => $item->scheduled_at,
                    'status' => $item->status === 'cancelled' ? 'cancelled' : ($item->status === 'completed' ? 'completed' : ($item->status === 'in_progress' ? 'in_progress' : 'scheduled')),
                ]
            );
        }

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

        if ($order->scheduled_at) {
            \App\Models\Appointment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'technician_id' => $order->technician_id,
                    'appointment_date' => $order->scheduled_at,
                    'status' => $order->status === 'cancelled' ? 'cancelled' : ($order->status === 'completed' ? 'completed' : ($order->status === 'in_progress' ? 'in_progress' : 'scheduled')),
                ]
            );
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
        
        \App\Models\Appointment::where('order_id', $item->id)->delete();

        return redirect()->back()->with('success', __('Order deleted successfully.'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->with('error', __('No orders selected.'));
        }

        Order::whereIn('id', $ids)->delete();
        \App\Models\Appointment::whereIn('order_id', $ids)->delete();

        return back()->with('success', __('Selected orders deleted successfully.'));
    }
    public function getAvailableTechnicians(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $service = $order->service;
        $categoryId = $service?->parent_id ?? $service?->id;
        $cityId = $order->city_id ?? ($order->user->city_id ?? null);

        $query = Technician::with(['user', 'service', 'category'])
            ->whereHas('user', function($q) use ($cityId) {
                // If order has a city, show techs in that city OR techs with no city specified (all cities)
                $q->when($cityId, function($sub) use ($cityId) {
                    $sub->where(function($cityQuery) use ($cityId) {
                        $cityQuery->where('city_id', $cityId)
                                 ->orWhereNull('city_id');
                    });
                })
                ->where('status', 'active');
            })
            ->where(function($q) use ($order, $categoryId) {
                // Strictly match technician's service or category with the order's service or category
                $targetServiceId = $order->service_id;
                $q->where('service_id', $targetServiceId)
                  ->orWhere('category_id', $categoryId)
                  ->orWhere('category_id', $targetServiceId)
                  ->orWhere('service_id', $categoryId)
                  ->orWhereJsonContains('service_ids', (int)$targetServiceId)
                  ->orWhereJsonContains('service_ids', (string)$targetServiceId);
            });

        // Availability check (only if order has a scheduled time)
        if ($order->scheduled_at) {
            $start = $order->scheduled_at->copy()->subHours(1)->addMinute();
            $end = $order->scheduled_at->copy()->addHours(1)->subMinute();
            
            $query->whereDoesntHave('orders', function($q) use ($start, $end, $order) {
                $q->where('id', '!=', $order->id)
                  ->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                  ->whereBetween('scheduled_at', [$start, $end]);
            })
            ->whereDoesntHave('appointments', function($q) use ($start, $end, $order) {
                $q->where('order_id', '!=', $order->id)
                  ->whereIn('status', ['scheduled', 'in_progress'])
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

                $serviceName = $tech->service->name_ar 
                    ?? ($tech->category->name_ar ?? __('Technician'));

                return [
                    'id' => $tech->id,
                    'name' => $tech->user->name,
                    'avatar' => $tech->user->avatar_url,
                    'specialty' => $serviceName,
                    'service_name' => $serviceName,
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
                return $q->where(function($query) use ($cityId) {
                    $query->where('city_id', $cityId)
                          ->orWhereNull('city_id');
                });
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

    public function cancel($id)
    {
        $order = Order::findOrFail($id);
        $order->status = 'cancelled';
        $order->save();

        \App\Models\Appointment::where('order_id', $order->id)->update([
            'status' => 'cancelled',
        ]);

        // Auto refund any completed payments to user wallet
        $totalPaid = (float) $order->payments()->where('status', 'completed')->sum('amount');
        if ($totalPaid > 0 && $order->user) {
            $user = $order->user;
            $user->wallet_balance = ($user->wallet_balance ?? 0) + $totalPaid;
            $user->save();

            \App\Models\WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $totalPaid,
                'type' => 'refund',
                'note' => 'استرداد قيمة الطلب الملغي رقم #' . $order->order_number . ' إلى المحفظة',
                'reference_id' => $order->id,
                'reference_type' => Order::class,
            ]);

            $order->payments()->where('status', 'completed')->update(['status' => 'refunded']);
        }

        if ($order->user_id) {
            $refundMsgAr = ($totalPaid > 0) ? " وتم استرداد مبلغ {$totalPaid} ريال إلى محفظتك بنجاح." : "";
            $refundMsgEn = ($totalPaid > 0) ? " and {$totalPaid} SAR has been refunded to your wallet." : "";
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_CANCELLED,
                'title_ar' => 'تم إلغاء الطلب',
                'title_en' => 'Order Cancelled',
                'body_ar' => "تم إلغاء طلبك رقم #{$order->order_number} من قبل الإدارة" . $refundMsgAr,
                'body_en' => "Your order #{$order->order_number} has been cancelled by administration" . $refundMsgEn,
                'data' => [
                    'order_id' => $order->id,
                    'refunded' => $totalPaid > 0,
                    'refund_amount' => $totalPaid,
                ]
            ]);
        }

        if ($order->technician_id && $order->technician && $order->technician->user_id) {
            $this->sendNotification($order->technician->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_CANCELLED,
                'title_ar' => 'تم إلغاء المهمة',
                'title_en' => 'Task Cancelled',
                'body_ar' => "تم إلغاء المهمة رقم #{$order->order_number} من قبل الإدارة",
                'body_en' => "Task #{$order->order_number} has been cancelled by administration",
                'data' => ['order_id' => $order->id]
            ]);
        }

        // Release technician availability
        if ($order->technician) {
            $order->technician->update(['availability_status' => 'available']);
        }

        return redirect()->back()->with('success', __('Order cancelled successfully.'));
    }

    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'scheduled_at' => 'required|date',
            'technician_id' => 'nullable|exists:technicians,id',
            'maintenance_company_id' => 'nullable|exists:maintenance_companies,id',
        ]);

        $order = Order::findOrFail($id);
        
        // Save the first original scheduled date if not set yet
        if (!$order->original_scheduled_at && $order->scheduled_at) {
            $order->original_scheduled_at = $order->scheduled_at;
        }

        $order->scheduled_at = $request->scheduled_at;

        // Handle technician / company assignment
        if ($request->filled('technician_id')) {
            $order->technician_id = $request->technician_id;
            $order->maintenance_company_id = null;
            $tech = \App\Models\Technician::find($request->technician_id);
            $order->status = ($tech && !$tech->maintenance_company_id) ? 'scheduled' : 'new';
            $order->assigned_at = now();
        } elseif ($request->filled('maintenance_company_id')) {
            $order->maintenance_company_id = $request->maintenance_company_id;
            $order->technician_id = null;
            $order->status = 'new';
            $order->assigned_at = now();
        } elseif ($order->technician_id) {
            $tech = $order->technician;
            $order->status = ($tech && !$tech->maintenance_company_id) ? 'scheduled' : 'new';
        } elseif ($order->maintenance_company_id) {
            $order->status = 'new';
        } elseif ($order->status === 'expired') {
            $order->status = $order->technician_id ? 'scheduled' : 'new';
        }

        $order->sub_status = null; // Clear expired/reschedule_requested sub_status
        $order->save();

        \App\Models\Appointment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'technician_id' => $order->technician_id,
                'appointment_date' => $order->scheduled_at,
                'status' => in_array($order->status, ['cancelled', 'completed', 'in_progress']) ? $order->status : 'scheduled',
            ]
        );

        // Notify client
        if ($order->user_id) {
            $formattedTime = \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d g:i A');
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED,
                'title_ar' => 'إعادة جدولة الطلب',
                'title_en' => 'Order Rescheduled',
                'body_ar' => "تم تغيير موعد طلبك رقم #{$order->order_number} إلى {$formattedTime}",
                'body_en' => "Your order #{$order->order_number} has been rescheduled to {$formattedTime}",
                'data' => ['order_id' => $order->id]
            ]);
        }

        // Notify technician if assigned
        if ($order->technician_id) {
            $tech = \App\Models\Technician::find($order->technician_id);
            if ($tech && $tech->user && !$tech->maintenance_company_id) {
                $formattedTime = \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d g:i A');
                $duration = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);
                $this->sendNotification($tech->user_id, [
                    'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED,
                    'title_ar' => 'تعديل موعد المهمة',
                    'title_en' => 'Task Rescheduled',
                    'body_ar' => "تم تغيير موعد المهمة رقم #{$order->order_number} إلى {$formattedTime}",
                    'body_en' => "Task #{$order->order_number} has been rescheduled to {$formattedTime}",
                    'data' => ['order_id' => $order->id]
                ]);
            }
        }

        // Notify maintenance company if assigned
        if ($order->maintenance_company_id) {
            $company = \App\Models\MaintenanceCompany::find($order->maintenance_company_id);
            if ($company && $company->user) {
                $formattedTime = \Carbon\Carbon::parse($order->scheduled_at)->format('Y-m-d g:i A');
                $this->sendNotification($company->user_id, [
                    'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED,
                    'title_ar' => 'تعديل موعد طلب الصيانة',
                    'title_en' => 'Maintenance Request Rescheduled',
                    'body_ar' => "تم تعديل موعد طلب الصيانة رقم #{$order->order_number} إلى {$formattedTime}",
                    'body_en' => "Maintenance request #{$order->order_number} has been rescheduled to {$formattedTime}",
                    'data' => ['order_id' => $order->id]
                ]);
            }
        }

        return redirect()->back()->with('success', __('Order rescheduled successfully.'));
    }

    private function sendNotification($userId, $details)
    {
        $user = \App\Models\User::find($userId);
        if ($user) {
            \App\Models\Notification::create([
                'user_id' => $userId,
                'type' => $details['type'] ?? 'notification',
                'target_audience' => 'individual_user',
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
