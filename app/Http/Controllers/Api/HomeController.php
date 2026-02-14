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
            // Customer Home - Return 3 main keys
            
            // 1. Follow Request - Latest active order
            $activeOrder = Order::with(['technician.user', 'service', 'city'])
                ->where('user_id', $user->id)
                ->whereIn('status', ['new', 'accepted', 'assigned', 'scheduled', 'in_progress'])
                ->latest()
                ->first();
            
            // Fetch Favorite IDs once
            $userFavorites = auth()->check() ? $user->favorites()->pluck('service_id')->toArray() : [];

            // 2. Service Categories - Limit to 6
            $categories = Service::with(['children', 'city'])->whereNull('parent_id')
                ->take(6)
                ->get()
                ->map(function ($service) use ($userFavorites) {
                    $service->is_favorite = in_array($service->id, $userFavorites);
                    return $service;
                });
            $totalCategories = Service::whereNull('parent_id')->count();
            
            // 3. Featured Services (أبرز الخدمات) - Limit to 6
            $featuredServices = Service::with(['children', 'city'])->whereNotNull('parent_id')
                ->where('is_featured', true)
                ->take(6)
                ->get();
            
            // If no featured services, get first 6 services
            if ($featuredServices->isEmpty()) {
                $featuredServices = Service::with(['children', 'city'])->whereNotNull('parent_id')
                    ->take(6)
                    ->get();
            }

            // Map favorite status to services
            $featuredServices = $featuredServices->map(function ($service) use ($userFavorites) {
                $service->is_favorite = in_array($service->id, $userFavorites);
                return $service;
            });
            
            $totalFeaturedServices = Service::whereNotNull('parent_id')
                ->where('is_featured', true)
                ->count();
            
            // If no featured services exist, count all services
            if ($totalFeaturedServices == 0) {
                $totalFeaturedServices = Service::whereNotNull('parent_id')->count();
            }
            
            $data['follow_request'] = $activeOrder;
            $data['categories'] = [
                'items' => $categories,
                'total' => $totalCategories,
                'showing' => $categories->count(),
                'has_more' => $totalCategories > 6
            ];
            $data['featured_services'] = [
                'items' => $featuredServices,
                'total' => $totalFeaturedServices,
                'showing' => $featuredServices->count(),
                'has_more' => $totalFeaturedServices > 6
            ];

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
            return response()->json([
                'status' => true,
                'message' => 'Please use /api/technician/home for technician dashboard',
                'data' => [
                    'redirect_to' => url('/api/technician/home')
                ]
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Home data retrieved successfully',
            'data' => $data
        ]);
    }
}
