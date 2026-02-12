<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Technician;
use App\Models\Service;
use App\Models\MaintenanceCompany;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function login()
    {
        if (Auth::check() && in_array(Auth::user()->type, ['supervisor', 'admin'])) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function postLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:9',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $phone = $request->phone;
        // Normalize phone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
        if (str_starts_with($phone, '0')) $phone = substr($phone, 1);

        $user = User::where('phone', $phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', __('Invalid credentials'))->withInput();
        }

        if (!in_array($user->type, ['supervisor', 'admin'])) {
            return redirect()->back()->with('error', __('Unauthorized access. Admin only.'))->withInput();
        }

        // Generate Static OTP for testing
        $otp = '0000';
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // In a real app, send SMA here. For now, log it.
        \Log::info("Admin OTP for {$phone}: {$otp}");
        
        Session::put('pending_admin_phone', $phone);
        Session::put('admin_remember', $request->has('remember'));

        return redirect()->route('admin.verify')->with('success', __('OTP sent to your phone.'));
    }

    public function forgotPassword()
    {
        return view('admin.forgot-password');
    }

    public function postForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:9',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $phone = $request->phone;
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
        if (str_starts_with($phone, '0')) $phone = substr($phone, 1);

        $user = User::where('phone', $phone)->whereIn('type', ['supervisor', 'admin'])->first();

        if (!$user) {
            return redirect()->back()->with('error', __('Admin phone not found.'))->withInput();
        }

        $otp = '0000';
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        Session::put('pending_admin_phone', $phone);
        Session::put('forgot_password_mode', true);

        return redirect()->route('admin.verify')->with('success', __('Reset OTP sent to your phone.'));
    }

    public function resetPassword()
    {
        if (!Session::has('verified_admin_phone')) {
            return redirect()->route('admin.login');
        }
        return view('admin.reset-password');
    }

    public function postResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $phone = Session::get('verified_admin_phone');
        $user = User::where('phone', $phone)->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
            Session::forget(['verified_admin_phone', 'forgot_password_mode']);
            return redirect()->route('admin.success')->with('success', __('Password updated successfully.'));
        }

        return redirect()->route('admin.login');
    }

    public function verify()
    {
        if (!Session::has('pending_admin_phone')) {
            return redirect()->route('admin.login');
        }
        return view('admin.verify');
    }

    public function postVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|array|size:4',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', __('Please enter a valid 4-digit OTP.'));
        }

        $inputOtp = implode('', $request->otp);
        $phone = Session::get('pending_admin_phone');
        $user = User::where('phone', $phone)->first();

        if (!$user || $user->otp !== $inputOtp || Carbon::now()->gt($user->otp_expires_at)) {
            return redirect()->back()->with('error', __('Invalid or expired OTP.'));
        }

        // OTP Verified
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        if (Session::has('forgot_password_mode')) {
            Session::put('verified_admin_phone', $phone);
            Session::forget('pending_admin_phone');
            return redirect()->route('admin.reset-password');
        }
        
        Session::put('admin_fully_verified', true);
        Auth::login($user, Session::get('admin_remember', false));
        Session::forget(['pending_admin_phone', 'admin_remember']);
        return redirect()->route('admin.success');
    }

    public function success()
    {
        if (!Session::has('admin_fully_verified') && !Session::has('success')) {
            return redirect()->route('admin.login');
        }
        Session::forget('admin_fully_verified');
        return view('admin.success');
    }

    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'daily');
        $revenue_period = $request->get('revenue_period', $period);
        $users_period = $request->get('users_period', $period);
        
        // 1. Statistics Cards with Comparison
        $now = Carbon::now();
        $compareDate = match($period) {
            'monthly' => $now->copy()->subMonth(),
            'yearly' => $now->copy()->subYear(),
            default => $now->copy()->subWeek(),
        };

        // Helper for percentage change
        $getChange = function ($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return (($current - $previous) / $previous) * 100;
        };

        // Available Technicians
        $availableTechsCount = Technician::where('availability_status', 'available')->count();
        $availableTechsPrevious = Technician::where('availability_status', 'available')
            ->where('created_at', '<', $compareDate)->count();
        $availableTechsChange = $getChange($availableTechsCount, $availableTechsPrevious);

        // Revenue
        $currentRevenue = Order::where('status', 'completed')->sum('total_price');
        $revenueThisPeriod = Order::where('status', 'completed')
            ->where('created_at', '>=', $compareDate)->sum('total_price');
        
        $prevCompareDate = match($period) {
            'monthly' => $compareDate->copy()->subMonth(),
            'yearly' => $compareDate->copy()->subYear(),
            default => $compareDate->copy()->subWeek(),
        };
        $revenueLastPeriod = Order::where('status', 'completed')
            ->whereBetween('created_at', [$prevCompareDate, $compareDate])->sum('total_price');
        $revenueChange = $getChange($revenueThisPeriod, $revenueLastPeriod);

        // New Orders
        $newOrdersCount = Order::where('status', 'new')->count();
        $newOrdersThisPeriod = Order::where('status', 'new')->where('created_at', '>=', $compareDate)->count();
        $newOrdersLastPeriod = Order::where('status', 'new')
            ->whereBetween('created_at', [$prevCompareDate, $compareDate])->count();
        $newOrdersChange = $getChange($newOrdersThisPeriod, $newOrdersLastPeriod);

        // Average Quality
        $avgRating = \App\Models\Review::avg('rating') ?? 0;
        $avgRatingLastPeriod = \App\Models\Review::where('created_at', '<', $compareDate)->avg('rating') ?? 0;
        $ratingChange = $getChange($avgRating, $avgRatingLastPeriod);

        $stats = [
            'available_techs' => $availableTechsCount,
            'available_change' => round($availableTechsChange, 2),
            'new_orders' => $newOrdersCount,
            'new_orders_change' => round($newOrdersChange, 2),
            'total_revenue' => $currentRevenue,
            'revenue_change' => round($revenueChange, 2),
            'avg_quality' => round($avgRating, 1),
            'quality_change' => round($ratingChange, 2),
            'revenue_period' => $revenue_period,
            'users_period' => $users_period,
        ];

        // Revenue Chart Data
        $revenue_limit = match($revenue_period) {
            'monthly' => 30,
            'yearly' => 12,
            default => 7,
        };
        for ($i = $revenue_limit - 1; $i >= 0; $i--) {
            if ($revenue_period == 'yearly') {
                $date = Carbon::now()->subMonths($i);
                $revenueChart[] = Order::where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total_price');
                $revenueLabels[] = $date->locale(App::getLocale())->monthName;
            } else {
                $date = Carbon::now()->subDays($i);
                $revenueChart[] = Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total_price');
                $revenueLabels[] = $revenue_limit == 7 ? $date->locale(App::getLocale())->dayName : $date->format('d/m');
            }
        }

        // Users Chart Data - Grouped by type
        $users_limit = match($users_period) {
            'monthly' => 30,
            'yearly' => 12,
            default => 7,
        };
        
        $individualsData = [];
        $corporateData = [];
        $techniciansData = [];
        $usersLabels = [];
        
        for ($i = $users_limit - 1; $i >= 0; $i--) {
            if ($users_period == 'yearly') {
                $date = Carbon::now()->subMonths($i);
                $individualsData[] = User::where('type', 'individual')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $corporateData[] = User::where('type', 'corporate_customer')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $techniciansData[] = User::where('type', 'technician')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $usersLabels[] = $date->locale(App::getLocale())->monthName;
            } else {
                $date = Carbon::now()->subDays($i);
                $individualsData[] = User::where('type', 'individual')
                    ->whereDate('created_at', $date)
                    ->count();
                $corporateData[] = User::where('type', 'corporate_customer')
                    ->whereDate('created_at', $date)
                    ->count();
                $techniciansData[] = User::where('type', 'technician')
                    ->whereDate('created_at', $date)
                    ->count();
                $usersLabels[] = $users_limit == 7 ? $date->locale(App::getLocale())->dayName : $date->format('d/m');
            }
        }

        // Top Services fallback
        $topServices = Order::select('service_id', DB::raw('count(*) as count'))
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->take(5)
            ->with('service')
            ->get();

        $serviceLabels = $topServices->pluck('service.name_' . App::getLocale());
        $serviceData = $topServices->pluck('count');

        // 3. Top Technicians
        $topTechnicians = Technician::with(['user', 'category'])
            ->withCount(['orders' => function($q) {
                $q->where('status', 'completed');
            }])
            ->orderByDesc('orders_count')
            ->take(5)
            ->get();

        // 4. Recent Orders (Initial load)
        $recent_orders = Order::with(['user', 'service', 'technician'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 
            'recent_orders', 
            'revenueChart', 
            'revenueLabels', 
            'usersLabels',
            'individualsData',
            'corporateData',
            'techniciansData',
            'serviceLabels', 
            'serviceData',
            'topTechnicians',
            'period'
        ));
    }

    public function dashboardOrders(Request $request)
    {
        $status = $request->get('status', 'all');
        $query = Order::with(['user', 'service', 'technician']);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $recent_orders = $query->latest()->take(10)->get();
        
        return view('admin.dashboard-orders-table', compact('recent_orders'))->render();
    }

    public function dashboardCategories(Request $request)
    {
        $type = $request->get('type', 'all'); // 'all', 'individual', 'corporate_customer'
        
        $query = Order::query();
        
        if ($type !== 'all') {
            $query->whereHas('user', function($q) use ($type) {
                $q->where('type', $type);
            });
        }
        
        $topServices = $query->select('service_id', DB::raw('count(*) as count'))
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->take(5)
            ->with('service')
            ->get();

        $labels = $topServices->pluck('service.name_' . App::getLocale())->values();
        $data = $topServices->pluck('count')->values();
        
        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'type' => $type
        ]);
    }

    public function dashboardRevenue(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $locale = $request->get('locale', App::getLocale());
        
        $revenueChart = [];
        $revenueLabels = [];
        
        $limit = match($period) {
            'monthly' => 30,
            'yearly' => 12,
            default => 7,
        };
        
        for ($i = $limit - 1; $i >= 0; $i--) {
            if ($period == 'yearly') {
                $date = Carbon::now()->subMonths($i);
                $revenueChart[] = Order::where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total_price');
                $revenueLabels[] = $date->locale($locale)->monthName;
            } else {
                $date = Carbon::now()->subDays($i);
                $revenueChart[] = Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total_price');
                $revenueLabels[] = $limit == 7 ? $date->locale($locale)->dayName : $date->format('d/m');
            }
        }
        
        return response()->json([
            'labels' => $revenueLabels,
            'data' => $revenueChart
        ]);
    }

    public function dashboardUsers(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $locale = $request->get('locale', App::getLocale());
        
        $individualsData = [];
        $corporateData = [];
        $techniciansData = [];
        $usersLabels = [];
        
        $limit = match($period) {
            'monthly' => 30,
            'yearly' => 12,
            default => 7,
        };
        
        for ($i = $limit - 1; $i >= 0; $i--) {
            if ($period == 'yearly') {
                $date = Carbon::now()->subMonths($i);
                $individualsData[] = User::where('type', 'individual')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $corporateData[] = User::where('type', 'corporate_customer')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $techniciansData[] = User::where('type', 'technician')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $usersLabels[] = $date->locale($locale)->monthName;
            } else {
                $date = Carbon::now()->subDays($i);
                $individualsData[] = User::where('type', 'individual')
                    ->whereDate('created_at', $date)
                    ->count();
                $corporateData[] = User::where('type', 'corporate_customer')
                    ->whereDate('created_at', $date)
                    ->count();
                $techniciansData[] = User::where('type', 'technician')
                    ->whereDate('created_at', $date)
                    ->count();
                $usersLabels[] = $limit == 7 ? $date->locale($locale)->dayName : $date->format('d/m');
            }
        }
        
        return response()->json([
            'labels' => $usersLabels,
            'individuals' => $individualsData,
            'corporate' => $corporateData,
            'technicians' => $techniciansData
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function switchLanguage($lang)
    {
        if (in_array($lang, ['ar', 'en'])) {
            Session::put('locale', $lang);
            App::setLocale($lang);
        }
        return redirect()->back();
    }

    public function markNotificationRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }

    public function notifications()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(10);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAllNotificationsRead()
    {
        Auth::user()->unreadNotifications()->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function deleteNotification($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->delete();
        }
        return redirect()->back()->with('success', __('Notification deleted successfully.'));
    }
}
