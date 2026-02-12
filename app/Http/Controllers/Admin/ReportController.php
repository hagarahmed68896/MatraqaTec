<?php

namespace App\Http\Controllers\Admin;

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
        $availableChange = $totalTechs > 0 ? round(($availableTechs / $totalTechs) * 100, 2) : 0;

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

        // 2. Charts Data
        $revenueTrend = [];
        $usersTrend = [];
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $days[] = $now->copy()->subDays($i)->translatedFormat('l');

            $revenueTrend[] = Payment::where('status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('amount');

            $usersTrend[] = User::whereDate('created_at', $date)->count();
        }

        // 3. Prominent Technicians
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

        // 4. Service Distribution
        $serviceDistribution = Order::select('service_id', DB::raw('count(*) as count'))
            ->whereNotNull('service_id')
            ->with('service')
            ->groupBy('service_id')
            ->get();

        $data = [
            'header_stats' => [
                'new_orders' => [
                    'count' => $currentOrders,
                    'change' => $ordersChange,
                    'trend' => array_slice($revenueTrend, -7)
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
        ];

        return view('admin.reports.index', compact('data'));
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
        $distribution = User::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        $trendQuery = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc');
        
        if ($request->has('date_from')) {
            $trendQuery->whereDate('created_at', '>=', $request->input('date_from'));
        } else {
            $trendQuery->whereDate('created_at', '>=', Carbon::now()->subDays(30));
        }
        
        $trend = $trendQuery->get();

        $cities = User::select('city_id', DB::raw('count(*) as count'))
            ->whereNotNull('city_id')
            ->with(['city' => function($q) {
                $q->select('id', 'name_ar', 'name_en');
            }])
            ->groupBy('city_id')
            ->orderByDesc('count')
            ->get();

        return view('admin.reports.users', compact('distribution', 'trend', 'cities'));
    }

    // Financial Report
    public function financials(Request $request)
    {
        $revenueQuery = Payment::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
            ->where('status', 'paid')
            ->groupBy('date')
            ->orderBy('date', 'asc');

        if ($request->has('date_from')) {
            $revenueQuery->whereDate('created_at', '>=', $request->input('date_from'));
        } else {
            $revenueQuery->whereDate('created_at', '>=', Carbon::now()->subDays(30));
        }
        $revenueTrend = $revenueQuery->get();

        $settlements = FinancialSettlement::select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
            ->groupBy('status')
            ->get();

        $profitQuery = PlatformProfit::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc');
            
        if ($request->has('date_from')) {
            $profitQuery->whereDate('created_at', '>=', $request->input('date_from'));
        } else {
            $profitQuery->whereDate('created_at', '>=', Carbon::now()->subDays(30));
        }
        $profitTrend = $profitQuery->get();

        return view('admin.reports.financials', compact('revenueTrend', 'settlements', 'profitTrend'));
    }

    // Services Report
    public function services(Request $request)
    {
        $topServices = Order::select('service_id', DB::raw('count(*) as count'))
            ->whereNotNull('service_id')
            ->with('service')
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $statusDistribution = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return view('admin.reports.services', compact('topServices', 'statusDistribution'));
    }

    // Technicians Report
    public function technicians(Request $request)
    {
        $search = $request->input('search');

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

        return view('admin.reports.technicians', compact('topRated', 'mostActive'));
    }
}
