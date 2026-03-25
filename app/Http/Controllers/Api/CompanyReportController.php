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

        $filter = $request->input('period', 'monthly'); // monthly, weekly, yearly
        $locale = $request->header('Accept-Language', 'ar');

        $now = Carbon::now();
        
        if ($filter === 'weekly') {
            $currentStart = $now->copy()->startOfWeek();
            $previousStart = $currentStart->copy()->subWeek();
            $currentEnd = $now->copy()->endOfWeek();
            $previousEnd = $currentEnd->copy()->subWeek();
        } elseif ($filter === 'yearly') {
            $currentStart = $now->copy()->startOfYear();
            $previousStart = $currentStart->copy()->subYear();
            $currentEnd = $now->copy()->endOfYear();
            $previousEnd = $currentEnd->copy()->subYear();
        } else { // monthly
            $currentStart = $now->copy()->startOfMonth();
            $previousStart = $currentStart->copy()->subMonth();
            $currentEnd = $now->copy()->endOfMonth();
            $previousEnd = $currentEnd->copy()->subMonth();
        }

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

        // 2. Chart Data (Current vs Previous)
        $chartData = [];
        
        if ($filter === 'weekly') {
            // Days of the week
            for ($i = 0; $i < 7; $i++) {
                $date = $currentStart->copy()->addDays($i);
                $prevDate = $previousStart->copy()->addDays($i);

                $chartData[] = [
                    'label' => $locale == 'ar' ? $date->translatedFormat('D') : $date->format('D'),
                    'value' => Order::where('maintenance_company_id', $company->id)
                        ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                        ->count(),
                    'pre_value' => Order::where('maintenance_company_id', $company->id)
                        ->whereBetween('created_at', [$prevDate->copy()->startOfDay(), $prevDate->copy()->endOfDay()])
                        ->count(),
                ];
            }
        } elseif ($filter === 'yearly') {
            // Months of the year
            for ($i = 0; $i < 12; $i++) {
                $date = $currentStart->copy()->addMonths($i);
                $prevDate = $previousStart->copy()->addMonths($i);

                $chartData[] = [
                    'label' => $locale == 'ar' ? $date->translatedFormat('M') : $date->format('M'),
                    'value' => Order::where('maintenance_company_id', $company->id)
                        ->whereBetween('created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                        ->count(),
                    'pre_value' => Order::where('maintenance_company_id', $company->id)
                        ->whereBetween('created_at', [$prevDate->copy()->startOfMonth(), $prevDate->copy()->endOfMonth()])
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
                    'value' => Order::where('maintenance_company_id', $company->id)
                        ->whereBetween('created_at', [$start, $end])
                        ->count(),
                    'pre_value' => Order::where('maintenance_company_id', $company->id)
                        ->whereBetween('created_at', [$prevStart, $prevEnd])
                        ->count(),
                ];
            }
        }

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

        // 8. Top Technicians (by completed orders and rating)
        $topTechnicians = DB::table('orders')
            ->join('technicians', 'orders.technician_id', '=', 'technicians.id')
            ->join('users', 'technicians.user_id', '=', 'users.id')
            ->leftJoin('reviews', 'orders.id', '=', 'reviews.order_id')
            ->where('orders.maintenance_company_id', $company->id)
            ->where('orders.status', 'completed')
            ->select(
                'technicians.id',
                'users.name as name',
                'users.email as email',
                'users.phone as phone',
                'users.avatar as avatar',
                DB::raw('count(DISTINCT orders.id) as completed_orders'),
                DB::raw('sum(orders.total_price) as generated_revenue'),
                DB::raw('round(avg(reviews.rating), 1) as average_rating')
            )
            ->groupBy(
                'technicians.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.avatar'
            )
            ->orderByDesc('completed_orders')
            ->orderByDesc('average_rating')
            ->limit(5)
            ->get();

        // Format avatar URLs properly
        $topTechnicians->transform(function ($tech) {
            $tech->avatar = $tech->avatar ? asset('storage/' . $tech->avatar) : null;
            $tech->average_rating = $tech->average_rating ?? 0;
            return $tech;
        });

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
                'completion_rate' => round($completionRate, 2),
                'top_technicians' => $topTechnicians,
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

        // 3. Search by Query (Note, Order Number, Client Name, or Tech Name)
        if ($request->filled('query')) {
            $search = $request->get('query');
            $query->where(function($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhereHas('order', function($q2) use ($search) {
                      $q2->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function($q3) use ($search) {
                            $q3->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('technician.user', function($q4) use ($search) {
                            $q4->where('name', 'like', "%{$search}%");
                        });
                  });
            });
        }

        // 4. Sorting Logic
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

        // Add summary stats
        $stats = [
            'total_balance' => $user->wallet_balance ?? "0.00",
            'total_credit' => (clone $query)->where('type', 'credit')->sum('amount'),
            'total_debit' => (clone $query)->where('type', 'debit')->sum('amount'),
        ];

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
            'data' => $transactions,
            'stats' => $stats
        ]);
    }

    /**
     * Backfill wallet transactions for all completed orders (one-time use).
     * Call: GET /api/company/backfill-transactions
     */
    public function backfillTransactions(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        }

        $commissionRate = (float) \App\Models\Setting::getByKey('platform_commission', 15);

        $orders = \App\Models\Order::where('maintenance_company_id', $company->id)
            ->where('status', 'completed')
            ->where('total_price', '>', 0)
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($orders as $order) {
            $exists = WalletTransaction::where('reference_id', $order->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $companyShare = $order->total_price * (1 - ($commissionRate / 100));

            WalletTransaction::create([
                'user_id'        => $user->id,
                'amount'         => round($companyShare, 2),
                'type'           => 'deposit',
                'note'           => 'تحصيل دفعة للطلب رقم ' . $order->order_number,
                'reference_id'   => $order->id,
                'reference_type' => \App\Models\Order::class,
            ]);

            $created++;
        }

        return response()->json([
            'status'  => true,
            'message' => "تم إنشاء $created معاملة، تم تخطي $skipped معاملة موجودة مسبقاً",
            'data'    => [
                'created' => $created,
                'skipped' => $skipped,
            ]
        ]);
    }
}
