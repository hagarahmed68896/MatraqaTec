<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanyReportController extends Controller
{
    public function statistics(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'Company profile not found'], 404);
        }

        $filter = $request->input('period', 'monthly'); // monthly, weekly

        $now = Carbon::now();
        $currentStart = ($filter === 'weekly') ? $now->startOfWeek() : $now->startOfMonth();
        $previousStart = ($filter === 'weekly') ? $currentStart->copy()->subWeek() : $currentStart->copy()->subMonth();
        $previousEnd = $currentStart->copy()->subSecond();

        // 1. Order Count Summary
        $currentCount = Order::where('maintenance_company_id', $company->id)
            ->whereBetween('created_at', [$currentStart, $now])
            ->count();

        $previousCount = Order::where('maintenance_company_id', $company->id)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        $percentageChange = 0;
        if ($previousCount > 0) {
            $percentageChange = (($currentCount - $previousCount) / $previousCount) * 100;
        } elseif ($currentCount > 0) {
            $percentageChange = 100;
        }

        // 2. Chart Data (Last 4 units - weeks or months)
        $chartData = [];
        for ($i = 0; $i < 4; $i++) {
            $date = ($filter === 'weekly') ? $now->copy()->subWeeks($i) : $now->copy()->subMonths($i);
            $start = ($filter === 'weekly') ? $date->copy()->startOfWeek() : $date->copy()->startOfMonth();
            $end = ($filter === 'weekly') ? $date->copy()->endOfWeek() : $date->copy()->endOfMonth();

            $count = Order::where('maintenance_company_id', $company->id)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $chartData[] = [
                'label' => ($filter === 'weekly') ? "Week " . $date->weekOfYear : $date->format('M'),
                'value' => $count
            ];
        }
        $chartData = array_reverse($chartData);

        // 3. Top Services
        $topServices = DB::table('orders')
            ->join('services', 'orders.service_id', '=', 'services.id')
            ->where('orders.maintenance_company_id', $company->id)
            ->select('services.id', 'services.name_ar', 'services.name_en', 'services.image', DB::raw('count(orders.id) as order_count'))
            ->groupBy('services.id', 'services.name_ar', 'services.name_en', 'services.image')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get();

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
                'chart' => $chartData,
                'top_services' => $topServices
            ]
        ]);
    }

    public function transactions(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $query = WalletTransaction::where('user_id', $user->id)
            ->with(['order.service']);

        // 1. Filter by Categories (Multiple)
        if ($request->filled('category_ids')) {
            $categoryIds = is_array($request->category_ids) ? $request->category_ids : explode(',', $request->category_ids);
            $query->whereHas('order.service', function($q) use ($categoryIds) {
                $q->whereIn('parent_id', $categoryIds);
            });
        }

        // 2. Filter by Services (Multiple)
        if ($request->filled('service_ids')) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereHas('order', function($q) use ($serviceIds) {
                $q->whereIn('service_id', $serviceIds);
            });
        }

        // 3. Sorting Logic
        $sort = $request->input('sort_by', 'newest');
        switch ($sort) {
            case 'name':
                $query->join('orders', 'wallet_transactions.reference_id', '=', 'orders.id')
                    ->join('services', 'orders.service_id', '=', 'services.id')
                    ->orderBy('services.name_ar', 'asc')
                    ->select('wallet_transactions.*'); // Avoid column collision
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'highest_price':
                $query->orderBy('amount', 'desc');
                break;
            case 'lowest_price':
                $query->orderBy('amount', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $transactions = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions
        ]);
    }
}
