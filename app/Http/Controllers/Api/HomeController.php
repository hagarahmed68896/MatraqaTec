<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Content;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userType = $user->type;
        
        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'type' => $user->type,
                'avatar' => $user->avatar ? asset($user->avatar) : null,
                'city' => $user->city ? $user->city->name_ar : null,
            ],
            'unread_notifications_count' => Notification::where('user_id', $user->id)->unread()->count(),
        ];

        if (in_array($userType, ['individual', 'corporate_customer'])) {
            // Customer Home
            $data['banners'] = Content::with('items')->where('is_visible', true)->get();
            $data['categories'] = Service::whereNull('parent_id')->get();
            $data['prominent_services'] = Service::whereNotNull('parent_id')->where('is_featured', true)->take(4)->get();
            if ($data['prominent_services']->isEmpty()) {
                $data['prominent_services'] = Service::whereNotNull('parent_id')->take(4)->get();
            }
            
            // Latest active order for "Follow your order" section
            $data['active_order'] = Order::with(['technician.user', 'service', 'city'])
                ->where('user_id', $user->id)
                ->whereIn('status', ['new', 'accepted', 'assigned', 'scheduled', 'in_progress'])
                ->latest()
                ->first();

            $data['search_history'] = \App\Models\SearchHistory::where('user_id', $user->id)->latest()->take(5)->get();
            $data['cities'] = \App\Models\City::with('districts')->get();
            $data['categories'] = Service::whereNull('parent_id')->get();
            $data['service_types'] = Service::whereNotNull('parent_id')->get();
        } elseif ($userType === 'maintenance_company') {
            // Company Home
            $company = $user->maintenanceCompany;
            $filter = request('filter', 'all'); // weekly, monthly, yearly
            
            $query = Order::where('maintenance_company_id', $company->id ?? null);
            
            // Apply Date Filters for Statistics
            if ($filter === 'weekly') {
                $query->where('created_at', '>=', now()->subWeek());
            } elseif ($filter === 'monthly') {
                $query->where('created_at', '>=', now()->subMonth());
            } elseif ($filter === 'yearly') {
                $query->where('created_at', '>=', now()->subYear());
            }

            $ordersCount = (clone $query)->count();
            
            // Top Technicians (Top 3 based on completed orders)
            $topTechnicians = \App\Models\Technician::with(['user:id,name,avatar'])
                ->where('maintenance_company_id', $company->id ?? 0)
                ->withCount(['orders as completed_orders_count' => function($q) {
                    $q->where('status', 'completed');
                }])
                ->orderByDesc('completed_orders_count')
                ->take(3)
                ->get();

            $data['statistics'] = [
                'orders_count' => $ordersCount,
                'filter_type' => $filter
            ];
            
            $data['top_technicians'] = $topTechnicians;
            
            // Recent Orders for company dashboard
            $data['recent_orders'] = Order::with(['technician.user', 'service', 'user'])
                ->where('maintenance_company_id', $company->id ?? null)
                ->latest()
                ->take(5)
                ->get();

            // Current Active Order (one that might need action or is in progress)
            $data['current_order'] = Order::with(['user', 'service', 'technician.user'])
                ->where('maintenance_company_id', $company->id ?? null)
                ->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress'])
                ->latest()
                ->first();

            // For summary (Wallet, etc.)
            $data['summary'] = [
                'active_orders_count' => Order::where('maintenance_company_id', $company->id ?? null)->whereIn('status', ['new', 'accepted', 'in_progress', 'scheduled'])->count(),
                'total_completed_count' => Order::where('maintenance_company_id', $company->id ?? null)->where('status', 'completed')->count(),
                'wallet_balance' => $user->wallet_balance,
            ];
        } elseif ($userType === 'technician') {
            // Technician Home
            $technician = $user->technician;
            $data['user']['is_online'] = $user->is_online;
            $data['user']['specialty'] = $technician && $technician->service ? $technician->service->name_ar : ($technician && $technician->category ? $technician->category->name_ar : 'فني');

            // Order Tabs filtering
            $statusTab = request('tab', 'all'); 
            
            $assignedQuery = Order::where('technician_id', $technician->id ?? null);
            
            // For independent technicians, "New" orders are those in their city/specialty not yet assigned
            if ($technician && !$technician->maintenance_company_id) {
                $newOrdersQuery = Order::where('status', 'new')
                    ->where('city_id', $user->city_id)
                    ->where(function($q) use ($technician) {
                        $q->where('service_id', $technician->service_id)
                          ->orWhereHas('service', function($q2) use ($technician) {
                              $q2->where('parent_id', $technician->category_id);
                          });
                    });
            } else {
                $newOrdersQuery = (clone $assignedQuery)->where('status', 'new');
            }

            // Counts for tabs
            $data['counts'] = [
                'new' => (clone $newOrdersQuery)->count(),
                'in_progress' => (clone $assignedQuery)->whereIn('status', ['accepted', 'scheduled', 'in_progress'])->count(),
                'archived' => (clone $assignedQuery)->whereIn('status', ['completed', 'rejected', 'cancelled'])->count(),
            ];
            $data['counts']['all'] = $data['counts']['new'] + $data['counts']['in_progress'] + $data['counts']['archived'];

            if ($statusTab === 'new') {
                $query = $newOrdersQuery;
            } else {
                $query = clone $assignedQuery;
                if ($statusTab === 'in_progress') {
                    $query->whereIn('status', ['accepted', 'scheduled', 'in_progress']);
                } elseif ($statusTab === 'archived') {
                    $query->whereIn('status', ['completed', 'rejected', 'cancelled']);
                }
            }

            $orders = $query->with(['user', 'service.parent', 'city'])
                ->latest()
                ->paginate($request->per_page ?? 10);

            $data['orders'] = $orders;
            
            $data['summary'] = [
                'active_orders_count' => $data['counts']['in_progress'],
                'total_completed_count' => (clone $assignedQuery)->where('status', 'completed')->count(),
                'wallet_balance' => $user->wallet_balance,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Home data retrieved successfully',
            'data' => $data
        ]);
    }
}
