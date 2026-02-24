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
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Unified Reports Dashboard
    public function index(Request $request)
    {
        $now = Carbon::now();
        
        // Date Filtering
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to', $now->format('Y-m-d'));
        $categoryId = $request->input('category_id');
    $userType = $request->input('user_type'); // Global filter
    
    // Section-specific filters
    $revenueType = $request->input('revenue_type'); // all, individual, corporate_customer
    $settlementType = $request->input('settlement_type'); // all, company, technician
    $categoriesType = $request->input('categories_type'); // all, individual, corporate_customer
    $techType = $request->input('tech_type'); // all, platform, company

        if ($dateFrom) {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
            $daysRange = $startDate->diffInDays($endDate) + 1;
        } else {
            $daysRange = 7;
            $startDate = $now->copy()->subDays($daysRange - 1)->startOfDay();
            $endDate = $now->copy()->endOfDay();
        }
        
        // Category Filter for Orders/Technicians/Payments
        $orderQuery = Order::query();
        $techQuery = Technician::query();
        $paymentQuery = Payment::where('status', 'paid');
        $settlementQuery = FinancialSettlement::query();
        $reviewQuery = Review::query();
        $userQuery = User::query();

        if ($categoryId) {
            $orderQuery->where('service_id', $categoryId);
            $techQuery->where('category_id', $categoryId);
            $paymentQuery->whereHas('order', function($q) use ($categoryId) {
                $q->where('service_id', $categoryId);
            });
            $reviewQuery->whereHas('order', function($q) use ($categoryId) {
                $q->where('service_id', $categoryId);
            });
        }

        if ($userType) {
            $orderQuery->whereHas('user', function($q) use ($userType) {
                $q->where('type', $userType);
            });
            $paymentQuery->whereHas('user', function($q) use ($userType) {
                $q->where('type', $userType);
            });
            $userQuery->where('type', $userType);
            $reviewQuery->whereHas('order', function($q) use ($userType) {
                $q->whereHas('user', function($uq) use ($userType) {
                    $uq->where('type', $userType);
                });
            });
        }

        // 1. Users Statistics (Multi-series Bar Chart)
        $labels = [];
        $activeUsersTrend = [];
        $blockedUsersTrend = [];
        $newUsersTrend = [];
        
        for ($i = $daysRange - 1; $i >= 0; $i--) {
            $date = $startDate->copy()->addDays(($daysRange - 1) - $i)->format('Y-m-d');
            $labels[] = $startDate->copy()->addDays(($daysRange - 1) - $i)->translatedFormat('l');
            
            $activeUsersTrend[] = (clone $userQuery)->whereDate('created_at', '<=', $date)->where('status', '!=', 'blocked')->count();
            $blockedUsersTrend[] = (clone $userQuery)->whereDate('created_at', '<=', $date)->where('status', 'blocked')->count();
            $newUsersTrend[] = (clone $userQuery)->whereDate('created_at', $date)->count();
        }

        // 2. Users Distribution by City (Stacked Bar Chart)
        $cityDistribution = City::withCount(['users' => function($q) use ($startDate, $endDate, $userType) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
            if ($userType) {
                $q->where('type', $userType);
            }
        }])
        ->orderByDesc('users_count')
        ->limit(8)
        ->get();

        // 3. Financials (Revenue & Settlements Bar Charts)
        $revenueTrend = [];
        $settlementsTrend = [];
        for ($i = $daysRange - 1; $i >= 0; $i--) {
            $date = $startDate->copy()->addDays(($daysRange - 1) - $i)->format('Y-m-d');
            
            $revQuery = clone $paymentQuery;
        if ($revenueType && $revenueType !== 'all') {
            $revQuery->whereHas('user', fn($q) => $q->where('type', $revenueType));
        }
        $revenueTrend[] = $revQuery->whereDate('created_at', $date)->sum('amount');
        
        $setQuery = clone $settlementQuery;
        if ($settlementType === 'company') {
            $setQuery->whereNotNull('maintenance_company_id');
        } elseif ($settlementType === 'technician') {
            $setQuery->whereNull('maintenance_company_id');
        }
        $settlementsTrend[] = $setQuery->whereDate('created_at', $date)->sum('amount');
    }

        // 4. Service Categories Distribution (Donut Chart)
    $categories = Service::whereNull('parent_id')->withCount(['orders' => function($q) use ($startDate, $endDate, $userType, $categoriesType) {
        $q->whereBetween('created_at', [$startDate, $endDate]);
        $type = $categoriesType !== 'all' ? $categoriesType : $userType;
        if ($type) {
            $q->whereHas('user', fn($uq) => $uq->where('type', $type));
        }
    }])->get();
        
        $categoryData = [
            'labels' => $categories->pluck('name_' . app()->getLocale()),
            'counts' => $categories->pluck('orders_count'),
            'total_orders' => $categories->sum('orders_count')
        ];

        // 5. Service Level & Ratings (Gauge & Rating Breakdown)
        $avgRating = (clone $reviewQuery)->whereBetween('created_at', [$startDate, $endDate])->avg('rating') ?: 0;
        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratingBreakdown[$i] = (clone $reviewQuery)->where('rating', $i)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
        }
        $totalReviews = (clone $reviewQuery)->whereBetween('created_at', [$startDate, $endDate])->count();
        $serviceLevel = ($avgRating / 5) * 100;

        // 6. Technician Performance (Area Chart)
        $techActiveTrend = [];
        $techBusyTrend = [];
        for ($i = $daysRange - 1; $i >= 0; $i--) {
            // Simplified: Current state if no historical snapshots
            $techActiveTrend[] = (clone $techQuery)->where('availability_status', 'available')->count();
            $techBusyTrend[] = (clone $techQuery)->where('availability_status', 'busy')->count();
        }

        // 7. Top Technicians
        $topTechnicians = (clone $techQuery)->with(['user', 'service'])
            ->withAvg(['reviews' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }], 'rating')
            ->withCount(['orders' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->orderByDesc('reviews_avg_rating')
            ->limit(3)
            ->get();

        // 8. Spare Parts Analysis
        $sparePartsOrders = (clone $orderQuery)->whereBetween('created_at', [$startDate, $endDate])->whereNotNull('spare_parts_metadata')->get();
        $totalSparePartsCost = $sparePartsOrders->sum(function($order) {
            $meta = is_array($order->spare_parts_metadata) ? $order->spare_parts_metadata : json_decode($order->spare_parts_metadata, true);
            return collect($meta)->sum('price');
        });

        $data = [
            'labels' => $labels,
            'users' => [
                'active' => $activeUsersTrend,
                'blocked' => $blockedUsersTrend,
                'new' => $newUsersTrend,
                'total' => User::count()
            ],
            'cities' => [
                'labels' => $cityDistribution->pluck('name_' . app()->getLocale()),
                'counts' => $cityDistribution->pluck('users_count')
            ],
            'financials' => [
                'revenue' => $revenueTrend,
                'settlements' => $settlementsTrend,
                'total_revenue' => (clone $paymentQuery)->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
                'total_settlements' => (clone $settlementQuery)->whereBetween('created_at', [$startDate, $endDate])->sum('amount')
            ],
            'categories' => $categoryData,
            'service_quality' => [
                'level' => round($serviceLevel, 1),
                'avg' => round($avgRating, 1),
                'breakdown' => array_values($ratingBreakdown),
                'total_count' => $totalReviews
            ],
            'technicians' => [
                'active_trend' => $techActiveTrend,
                'busy_trend' => $techBusyTrend,
                'top' => $topTechnicians
            ],
            'spare_parts' => [
                'total_cost' => $totalSparePartsCost,
                'usage_count' => $sparePartsOrders->count()
            ],
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'category_id' => $categoryId,
                'user_type' => $userType,
                'revenue_type' => $revenueType,
                'settlement_type' => $settlementType,
                'categories_type' => $categoriesType,
                'tech_type' => $techType,
                'all_categories' => Service::whereNull('parent_id')->get()
            ]
        ];

        return view('admin.reports.index', compact('data'));
    }

    public function download(Request $request)
    {
        // Simple CSV Export of the summary data
        $filename = "report_export_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://temp', 'w+');
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
        
        fputcsv($handle, [__('Metric'), __('Value')]);
        
        // This is a simplified export. A more detailed one could be built per section.
        $data = $this->index($request)->getData()['data'];
        
        fputcsv($handle, [__('Total Revenue'), $data['financials']['total_revenue']]);
        fputcsv($handle, [__('Total Settlements'), $data['financials']['total_settlements']]);
        fputcsv($handle, [__('Total Orders'), $data['categories']['total_orders']]);
        fputcsv($handle, [__('Average Rating'), $data['service_quality']['avg']]);
        fputcsv($handle, [__('Spare Parts Cost'), $data['spare_parts']['total_cost']]);
        
        fseek($handle, 0);
        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
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
