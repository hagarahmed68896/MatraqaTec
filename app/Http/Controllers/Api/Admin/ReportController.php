<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PlatformProfit;
use App\Models\FinancialSettlement;
use App\Models\Service;
use App\Models\Technician;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // General Dashboard Summary
    public function index(Request $request)
    {
        $now = Carbon::now();
        $startOfCurrentWeek = $now->copy()->subDays(7);
        $startOfPreviousWeek = $now->copy()->subDays(14);

        // 1. Header Stats
        // New Orders
        $currentOrders = Order::where('created_at', '>=', $startOfCurrentWeek)->count();
        $previousOrders = Order::where('created_at', '>=', $startOfPreviousWeek)
            ->where('created_at', '<', $startOfCurrentWeek)
            ->count();
        $ordersChange = $this->calculatePercentageChange($currentOrders, $previousOrders);

        // Available Technicians
        $availableTechs = Technician::where('availability_status', 'available')->count();
        $totalTechs = Technician::count();
        $availableChange = $totalTechs > 0 ? round(($availableTechs / $totalTechs) * 100, 2) : 0; // Or comparison to last week available

        // Total Revenue
        $currentRevenue = Payment::where('status', 'paid')
            ->where('created_at', '>=', $startOfCurrentWeek)
            ->sum('amount');
        $previousRevenue = Payment::where('status', 'paid')
            ->where('created_at', '>=', $startOfPreviousWeek)
            ->where('created_at', '<', $startOfCurrentWeek)
            ->sum('amount');
        $revenueChange = $this->calculatePercentageChange($currentRevenue, $previousRevenue);

        // Average Rating
        $currentRating = Review::where('created_at', '>=', $startOfCurrentWeek)->avg('rating') ?? 0;
        $previousRating = Review::where('created_at', '>=', $startOfPreviousWeek)
            ->where('created_at', '<', $startOfCurrentWeek)
            ->avg('rating') ?? 0;
        $ratingChange = $this->calculatePercentageChange($currentRating, $previousRating);

        // 2. Charts Data (Daily Trends for last 7 days)
        $revenueTrend = [];
        $usersTrend = [];
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $days[] = $now->copy()->subDays($i)->translatedFormat('l'); // Day name

            $revenueTrend[] = Payment::where('status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('amount');

            $usersTrend[] = User::whereDate('created_at', $date)->count();
        }

        // 3. Prominent Technicians (أبرز الفنيين)
        $prominentQuery = Technician::withAvg('reviews', 'rating')
            ->with(['user', 'service']);

        if ($request->has('search')) {
            $search = $request->search;
            $prominentQuery->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $prominentTechnicians = $prominentQuery->orderByDesc('reviews_avg_rating')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();

        // 4. Service Distribution (فئات الخدمات)
        $serviceDistribution = Order::select('service_id', DB::raw('count(*) as count'))
            ->whereNotNull('service_id')
            ->with('service')
            ->groupBy('service_id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'header_stats' => [
                    'new_orders' => [
                        'count' => $currentOrders,
                        'change' => $ordersChange,
                        'trend' => array_slice($revenueTrend, -7) // Placeholder for trend line
                    ],
                    'available_technicians' => [
                        'count' => $availableTechs,
                        'total' => $totalTechs,
                        'percentage' => $availableChange
                    ],
                    'total_revenue' => [
                        'amount' => $currentRevenue,
                        'change' => $revenueChange
                    ],
                    'average_rating' => [
                        'rating' => round($currentRating, 1),
                        'change' => $ratingChange
                    ]
                ],
                'charts' => [
                    'days' => $days,
                    'revenue' => $revenueTrend,
                    'users' => $usersTrend
                ],
                'prominent_technicians' => $prominentTechnicians,
                'service_distribution' => $serviceDistribution,
                'total_users' => User::count(),
                'total_orders' => Order::count(),
                'platform_profit' => PlatformProfit::sum('amount'),
            ]
        ]);
    }

    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    // Users Report
    public function users(Request $request)
    {
        // 1. Distribution by Type
        $distribution = User::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        // 2. Registration Trend (Last 30 days or filtered)
        $trendQuery = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc');
        
        if ($request->has('date_from')) {
            $trendQuery->whereDate('created_at', '>=', $request->input('date_from'));
        } else {
            // Default last 30 days
            $trendQuery->whereDate('created_at', '>=', Carbon::now()->subDays(30));
        }
        
        $trend = $trendQuery->get();

        // 3. User Distribution by City
        $cities = User::select('city_id', DB::raw('count(*) as count'))
            ->whereNotNull('city_id')
            ->with(['city' => function($q) {
                $q->select('id', 'name_ar', 'name_en');
            }])
            ->groupBy('city_id')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'distribution' => $distribution,
                'trend' => $trend,
                'users_by_city' => $cities
            ]
        ]);
    }

    // Financial Report
    public function financials(Request $request)
    {
        // 1. Revenue over time
        $revenueQuery = Payment::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
            ->where('status', 'paid') // Ensure we only count paid
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($request->has('date_from')) {
            $revenueQuery->whereDate('created_at', '>=', $request->input('date_from'));
        } else {
            $revenueQuery->whereDate('created_at', '>=', Carbon::now()->subDays(30));
        }
        $revenueTrend = $revenueQuery->get();

        // 2. Settlements Status
        $settlements = FinancialSettlement::select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('status')
            ->get();

        // 3. Platform Profit Trend
        $profitQuery = PlatformProfit::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc');
            
        if ($request->has('date_from')) {
            $profitQuery->whereDate('created_at', '>=', $request->input('date_from'));
        } else {
            $profitQuery->whereDate('created_at', '>=', Carbon::now()->subDays(30));
        }
        $profitTrend = $profitQuery->get();

        return response()->json([
            'status' => true,
            'data' => [
                'revenue_trend' => $revenueTrend,
                'settlements_summary' => $settlements,
                'profit_trend' => $profitTrend
            ]
        ]);
    }

    // Services Report
    public function services(Request $request)
    {
        // 1. Top Requested Services
        $topServices = Order::select('service_id', DB::raw('count(*) as count'))
            ->whereNotNull('service_id')
            ->with('service')
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // 2. Service Status Distribution
        $statusDistribution = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'top_services' => $topServices,
                'status_distribution' => $statusDistribution
            ]
        ]);
    }

    // Technicians Report
    public function technicians(Request $request)
    {
        $search = $request->input('search');

        // 1. Top Rated Technicians
        $topRatedQuery = Technician::withAvg('reviews', 'rating');
        if ($search) {
            $topRatedQuery->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }
        $topRated = $topRatedQuery->orderByDesc('reviews_avg_rating')
            ->with('user', 'service')
            ->limit(10)
            ->get();

        // 2. Most Active Technicians (by Order Count)
        $mostActiveQuery = Technician::query();
        if ($search) {
            $mostActiveQuery->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }
        $mostActive = $mostActiveQuery->orderByDesc('order_count')
            ->with('user', 'service')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'top_rated' => $topRated,
                'most_active' => $mostActive
            ]
        ]);
    }
}
