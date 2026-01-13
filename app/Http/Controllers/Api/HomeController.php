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
    public function index()
    {
        $user = auth()->user();
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

        if (in_array($userType, ['individual', 'corporate_company'])) {
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
            $data['cities'] = \App\Models\City::all();
        } elseif (in_array($userType, ['technician', 'maintenance_company'])) {
            // Technician / Company Home
            $data['user']['is_online'] = $user->is_online;
            
            $query = Order::query();
            if ($userType === 'technician') {
                $query->where('technician_id', $user->technician->id ?? null);
            } else {
                $query->where('maintenance_company_id', $user->maintenanceCompany->id ?? null);
            }

            $data['summary'] = [
                'active_orders_count' => (clone $query)->whereIn('status', ['new', 'accepted', 'in_progress', 'scheduled'])->count(),
                'total_completed_count' => (clone $query)->where('status', 'completed')->count(),
                'wallet_balance' => $user->wallet_balance,
            ];
            
            // Maybe also add recent orders for technicians
            $data['recent_orders'] = $query->latest()->take(5)->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Home data retrieved successfully',
            'data' => $data
        ]);
    }
}
