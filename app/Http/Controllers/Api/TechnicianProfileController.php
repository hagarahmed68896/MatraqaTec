<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Technician;
use App\Models\User;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class TechnicianProfileController extends Controller
{
    /**
     * Get basic profile data
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();
        $technician = $user->technician()->with(['category', 'service'])->first();

        return response()->json([
            'status' => true,
            'message' => __('Profile data retrieved'),
            'data' => [
                'user' => $user,
                'technician' => $technician
            ]
        ]);
    }

    /**
     * Update profile (User and Technician/Company details)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'bank_address' => 'nullable|string',
            'iban' => 'nullable|string',
            'swift_code' => 'nullable|string',
        ]);

        // Update User
        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('phone')) {
            // Sanitize phone
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $user->phone = $phone;
        }
        
        if ($request->hasFile('avatar')) {
             if ($user->avatar) {
                 if (str_starts_with($user->avatar, 'uploads/')) {
                     if (file_exists(public_path($user->avatar))) {
                         unlink(public_path($user->avatar));
                     }
                 } else {
                     \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
                 }
             }
            $fileName = time() . '_' . $request->file('avatar')->getClientOriginalName();
            $request->file('avatar')->move(public_path('uploads/avatars'), $fileName);
            $user->avatar = 'uploads/avatars/' . $fileName;
        }
        $user->save();

        // Update Profile (Technician or Maintenance Company)
        $profileData = $request->only([
            'bio_ar', 'bio_en', 
            'bank_name', 'account_name', 'account_number', 'bank_address', 'iban', 'swift_code'
        ]);

        if ($user->type === 'technician') {
            $technician = $user->technician;
            if (!$technician) {
                $technician = \App\Models\Technician::create(['user_id' => $user->id]);
            }
            
            if ($request->filled('name')) {
                $profileData['name_ar'] = $request->name;
                $profileData['name_en'] = $request->name;
                $profileData['name']    = $request->name;
            }
            $technician->update($profileData);
            
            $user->load('city', 'technician.service', 'technician.category', 'technician.maintenanceCompany');
        } elseif ($user->type === 'maintenance_company') {
            $company = $user->maintenanceCompany;
            if (!$company) {
                $company = \App\Models\MaintenanceCompany::create(['user_id' => $user->id]);
            }

            if ($request->filled('name')) {
                $profileData['company_name_ar'] = $request->name;
                $profileData['company_name_en'] = $request->name;
            }
            $company->update($profileData);
            
            $user->load('maintenanceCompany.city', 'maintenanceCompany.services', 'maintenanceCompany.districts');
        }

        return response()->json([
            'status' => true,
            'message' => __('Profile updated successfully'),
            'data' => [
                'user' => $user,
                'profile' => ($user->type === 'technician') ? $user->technician : $user->maintenanceCompany
            ]
        ]);
    }

    /**
     * Update Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => true,
            'message' => __('Password changed successfully'),
        ]);
    }

    /**
     * Dashboard Statistics (Order Trends)
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $technician = $user->technician;
        if (!$technician) {
            return response()->json(['status' => false, 'message' => __('Technician profile not found')], 404);
        }

        $period = $request->input('period', 'monthly'); // weekly, monthly
        $locale = $request->header('Accept-Language', 'ar');

        $now = Carbon::now();
        
        if ($period === 'weekly') {
            $currentStart = $now->copy()->startOfWeek();
            $previousStart = $currentStart->copy()->subWeek();
            $currentEnd = $now->copy()->endOfWeek();
            $previousEnd = $currentEnd->copy()->subWeek();
        } else { // monthly
            $currentStart = $now->copy()->startOfMonth();
            $previousStart = $currentStart->copy()->subMonth();
            $currentEnd = $now->copy()->endOfMonth();
            $previousEnd = $currentEnd->copy()->subMonth();
        }

        // 1. Comparison Summary
        $currentCount = Order::where('technician_id', $technician->id)
            ->whereBetween('created_at', [$currentStart, $now])
            ->count();

        $previousCount = Order::where('technician_id', $technician->id)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        $percentageChange = 0;
        if ($previousCount > 0) {
            $percentageChange = (($currentCount - $previousCount) / $previousCount) * 100;
        } elseif ($currentCount > 0) {
            $percentageChange = 100;
        }

        // 2. Chart Data (Current period vs Previous)
        $chartData = [];
        if ($period === 'weekly') {
            // Days of the week
            for ($i = 0; $i < 7; $i++) {
                $date = $currentStart->copy()->addDays($i);
                $prevDate = $previousStart->copy()->addDays($i);

                $chartData[] = [
                    'label' => $locale == 'ar' ? $date->translatedFormat('D') : $date->format('D'),
                    'value' => Order::where('technician_id', $technician->id)
                        ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                        ->count(),
                    'pre_value' => Order::where('technician_id', $technician->id)
                        ->whereBetween('created_at', [$prevDate->copy()->startOfDay(), $prevDate->copy()->endOfDay()])
                        ->count(),
                ];
            }
        } else {
            // Weeks of the month (Up to 5 weeks)
            $totalWeeks = ceil($currentStart->diffInDays($currentEnd) / 7);
            for ($i = 0; $i < $totalWeeks; $i++) {
                $start = $currentStart->copy()->addWeeks($i);
                $end = $start->copy()->endOfWeek();
                if ($end->gt($currentEnd)) $end = $currentEnd->copy();

                $prevStart = $previousStart->copy()->addWeeks($i);
                $prevEnd = $prevStart->copy()->endOfWeek();
                if ($prevEnd->gt($previousEnd)) $prevEnd = $previousEnd->copy();

                $chartData[] = [
                    'label' => ($locale == 'ar' ? 'الأسبوع ' : 'Week ') . ($i + 1),
                    'value' => Order::where('technician_id', $technician->id)
                        ->whereBetween('created_at', [$start, $end])
                        ->count(),
                    'pre_value' => Order::where('technician_id', $technician->id)
                        ->whereBetween('created_at', [$prevStart, $prevEnd])
                        ->count(),
                ];
            }
        }

        return response()->json([
            'status' => true,
            'message' => __('Statistics retrieved successfully'),
            'data' => [
                'summary' => [
                    'current_count' => $currentCount,
                    'previous_count' => $previousCount,
                    'percentage_change' => round($percentageChange, 2),
                    'trend' => $percentageChange >= 0 ? 'up' : 'down'
                ],
                'chart' => $chartData
            ]
        ]);
    }

    /**
     * Wallet Transaction History
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        
        $query = WalletTransaction::where('user_id', $user->id)
            ->with(['order.service']);

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search by Order Number, Client Name, and Service Name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Search in note just in case
                $q->where('note', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($q2) use ($search) {
                      $q2->where('order_number', 'like', "%{$search}%")
                         ->orWhereHas('user', function ($q3) use ($search) {
                             $q3->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                         })
                         ->orWhereHas('service', function ($q4) use ($search) {
                             $q4->where('name_ar', 'like', "%{$search}%")
                                ->orWhere('name_en', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Filter by Service(s)
        if ($request->filled('service_ids')) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            
            $query->whereHas('order', function ($q) use ($serviceIds) {
                $q->whereIn('service_id', $serviceIds)
                  ->orWhereHas('services', function ($q2) use ($serviceIds) {
                      $q2->whereIn('services.id', $serviceIds);
                  });
            });
        }

        // Sorting Logic (الترتيب: الاحدث، الاقدم، الاعلي سعراً، الاقل سعراً)
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'oldest':
                    $query->oldest('created_at');
                    break;
                case 'highest_price':
                    $query->orderBy('amount', 'desc');
                    break;
                case 'lowest_price':
                    $query->orderBy('amount', 'asc');
                    break;
                case 'newest':
                default:
                    $query->latest('created_at');
                    break;
            }
        } else {
            $query->latest('created_at'); // Default sorting
        }

        $transactions = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'message' => __('Transactions retrieved successfully'),
            'data' => $transactions
        ]);
    }

    /**
     * Toggle Availability Status (Online/Offline)
     */
    public function toggleAvailability(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'technician') {
            return response()->json(['status' => false, 'message' => __('Unauthorized')], 403);
        }

        $request->validate([
            'is_online' => 'required|boolean'
        ]);

        $user->is_online = $request->is_online;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => __('Availability updated successfully'),
            'data' => ['is_online' => $user->is_online]
        ]);
    }
    /**
     * Technician Home Dashboard
     */
    public function home(Request $request)
    {
        $user = $request->user();
        $technician = $user->technician;
        
        $audienceType = 'technicians';

        $unreadCount = \App\Models\Notification::where(function ($query) use ($user, $audienceType) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('target_audience', [$audienceType, 'all']);
            })
            ->where('status', 'sent')
            ->whereDoesntHave('userActions', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where(function($query) {
                      $query->where('is_deleted', true)->orWhere('is_read', true);
                  });
            })
            ->where(function($query) {
                $query->where('is_read', false)->orWhereNull('user_id'); 
            })
            ->count();

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'type' => $user->type,
                'avatar' => $user->avatar_url,
                'city' => $user->city ? $user->city->name_ar : null,
                'is_online' => $user->is_online,
                'specialty' => $technician && $technician->service ? $technician->service->name_ar : ($technician && $technician->category ? $technician->category->name_ar : __('Technician')),
            ],
            'unread_notifications_count' => $unreadCount,
        ];

        // Order Tabs filtering
        $statusTab = $request->input('tab', 'all'); 
        
        // Auto-detach expired unaccepted orders for all technicians before retrieving the list
        $duration = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);
        \App\Models\Order::whereNotNull('technician_id')
            ->whereIn('status', ['new', 'scheduled'])
            ->where('assigned_at', '<', now()->subMinutes($duration))
            ->update([
                'technician_id' => null,
                'status' => 'new',
                'assigned_at' => null
            ]);

        $assignedQuery = Order::where('technician_id', $technician->id ?? null);

        // For independent technicians, "New" orders are those in their city/specialty not yet assigned
        if ($technician && !$technician->maintenance_company_id) {
            $newOrdersQuery = Order::where('status', 'new')
                ->where('city_id', $user->city_id)
                ->whereNull('technician_id') // Ensure it's not already assigned to someone else
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

        return response()->json([
            'status' => true,
            'message' => __('Technician home data retrieved successfully'),
            'data' => $data
        ]);
    }
}
