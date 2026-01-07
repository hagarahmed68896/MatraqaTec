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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // General Dashboard Summary
    public function index()
    {
        $data = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'), // Adjust 'paid' status as needed
            'platform_profit' => PlatformProfit::sum('amount'),
            'orders_today' => Order::whereDate('created_at', Carbon::today())->count(),
        ];

        return response()->json(['status' => true, 'data' => $data]);
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
        // 1. Top Rated Technicians
        // Relationship 'reviews' is defined in Technician model.
        $topRated = Technician::withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->with('user') // to get name
            ->limit(10)
            ->get();

        // 2. Most Active Technicians (by Order Count)
        $mostActive = Technician::orderByDesc('order_count')
            ->with('user')
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
