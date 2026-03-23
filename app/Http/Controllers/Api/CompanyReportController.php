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

        // 4. Financial Statistics (Completed Orders)
        $currentRevenue = Order::where('maintenance_company_id', $company->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$currentStart, $now])
            ->sum('total_price');

        $previousRevenue = Order::where('maintenance_company_id', $company->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total_price');

        $revenuePercentageChange = 0;
        if ($previousRevenue > 0) {
            $revenuePercentageChange = (($currentRevenue - $previousRevenue) / $previousRevenue) * 100;
        } elseif ($currentRevenue > 0) {
            $revenuePercentageChange = 100;
        }

        // 5. Orders by Status (Current Period)
        $statusDistribution = Order::where('maintenance_company_id', $company->id)
            ->whereBetween('created_at', [$currentStart, $now])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->all();

        // Ensure all statuses exist in the output with 0 if missing
        $allStatuses = ['new', 'accepted', 'scheduled', 'in_progress', 'completed', 'rejected', 'cancelled'];
        foreach ($allStatuses as $status) {
            if (!isset($statusDistribution[$status])) {
                $statusDistribution[$status] = 0;
            }
        }

        // 6. Active Technicians Count
        $activeTechniciansCount = $company->technicians()->where('availability_status', 'available')->count();

        // 7. Completion Rate
        $totalOrders = Order::where('maintenance_company_id', $company->id)
            ->whereBetween('created_at', [$currentStart, $now])
            ->count();
        $completedOrdersCount = $statusDistribution['completed'] ?? 0;
        $completionRate = $totalOrders > 0 ? ($completedOrdersCount / $totalOrders) * 100 : 0;

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
                'revenue' => [
                    'current_amount' => round($currentRevenue, 2),
                    'previous_amount' => round($previousRevenue, 2),
                    'percentage_change' => round($revenuePercentageChange, 2),
                    'trend' => $revenuePercentageChange >= 0 ? 'up' : 'down'
                ],
                'chart' => $chartData,
                'top_services' => $topServices,
                'status_distribution' => $statusDistribution,
                'active_technicians_count' => $activeTechniciansCount,
                'completion_rate' => round($completionRate, 2)
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
            ->with(['order.service', 'order.user', 'order.technician.user']);

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

        // Transform data to provide flatter structure for frontend if needed
        $transactions->getCollection()->transform(function($transaction) {
            return [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'type' => $transaction->type,
                'note' => $transaction->note,
                'created_at' => $transaction->created_at,
                'order_details' => $transaction->order ? [
                    'id' => $transaction->order->id,
                    'order_number' => $transaction->order->order_number,
                    'status' => $transaction->order->status,
                    'status_label' => $transaction->order->status_label,
                    'service_name' => $transaction->order->service->name_ar ?? $transaction->order->service->name_en ?? null,
                    'client_name' => $transaction->order->user->name ?? null,
                    'technician_name' => $transaction->order->technician->user->name ?? null,
                ] : null
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions
        ]);
    }
}
