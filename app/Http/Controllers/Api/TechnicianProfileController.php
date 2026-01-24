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
            'message' => 'Profile data retrieved',
            'data' => [
                'user' => $user,
                'technician' => $technician
            ]
        ]);
    }

    /**
     * Update profile (User and Technician details)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $technician = $user->technician;

        $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'bio_ar' => 'nullable|string',
            'bio_en' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'iban' => 'nullable|string',
            'swift_code' => 'nullable|string',
        ]);

        // Update User
        $userData = $request->only(['name', 'phone']);
        if ($request->hasFile('avatar')) {
            $userData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($userData);

        // Update Technician
        $technicianData = $request->only([
            'bio_ar', 'bio_en', 
            'bank_name', 'account_name', 'account_number', 'iban', 'swift_code'
        ]);
        $technician->update($technicianData);

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user->fresh(),
                'technician' => $technician->fresh()
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
            'message' => 'Password changed successfully',
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
            return response()->json(['status' => false, 'message' => 'Technician profile not found'], 404);
        }

        $period = $request->input('period', 'monthly'); // weekly, monthly

        $now = Carbon::now();
        $currentStart = ($period === 'weekly') ? $now->startOfWeek() : $now->startOfMonth();
        $previousStart = ($period === 'weekly') ? $currentStart->copy()->subWeek() : $currentStart->copy()->subMonth();
        $previousEnd = $currentStart->copy()->subSecond();

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

        // 2. Chart Data (Current period breakdown)
        $chartData = [];
        if ($period === 'weekly') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i);
                $chartData[] = [
                    'label' => $day->format('D'),
                    'value' => Order::where('technician_id', $technician->id)
                        ->whereDate('created_at', $day)
                        ->count()
                ];
            }
        } else {
            // Last 4 weeks
            for ($i = 3; $i >= 0; $i--) {
                $week = $now->copy()->subWeeks($i);
                $chartData[] = [
                    'label' => "Week " . (4 - $i),
                    'value' => Order::where('technician_id', $technician->id)
                        ->whereBetween('created_at', [$week->copy()->startOfWeek(), $week->copy()->endOfWeek()])
                        ->count()
                ];
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Statistics retrieved successfully',
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
            ->with(['order.service'])
            ->latest();

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'message' => 'Transactions retrieved successfully',
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
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'is_online' => 'required|boolean'
        ]);

        $user->is_online = $request->is_online;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Availability updated successfully',
            'data' => ['is_online' => $user->is_online]
        ]);
    }
}
