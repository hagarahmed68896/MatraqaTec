<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialSettlement;
use Illuminate\Http\Request;

class FinancialSettlementController extends Controller
{
    public function index(Request $request)
    {
        // 1. Statistics
        $stats = [
            'total_amount' => [
                'value' => FinancialSettlement::sum('amount'),
                'label' => 'إجمالي التسويات المالية'
            ],
            'pending_amount' => [
                'value' => FinancialSettlement::where('status', 'pending')->sum('amount'),
                'label' => 'تسويات معلقة'
            ],
            'transferred_amount' => [
                'value' => FinancialSettlement::where('status', 'transferred')->sum('amount'),
                'label' => 'تسويات محولة'
            ],
        ];

        // 2. Base Query with Filters
        $query = $this->filterQuery($request);

        // 3. Sorting
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'name':
                    // Sort by account name (Company or User)
                    // This is complex with two relations, simpler to sort by ID or rely on frontend or specific requirement.
                    // For now defaulting to newest if name sort is requested but implementing custom sort is too heavy.
                     $query->orderBy('created_at', 'desc');
                    break;
                case 'amount_high':
                     $query->orderBy('amount', 'desc');
                    break;
                case 'amount_low':
                     $query->orderBy('amount', 'asc');
                    break;
                default:
                    $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        // 4. Pagination
        $perPage = $request->input('limit', 10);
        $settlements = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Settlements retrieved',
            'data' => [
                'stats' => $stats,
                'settlements' => $settlements
            ]
        ]);
    }

    public function download(Request $request)
    {
        $query = $this->filterQuery($request);
        $settlements = $query->orderBy('id', 'desc')->get();

        $filename = "settlements_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        // Add UTF-8 BOM
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Headers
        fputcsv($handle, [
            'ID',
            'Account Name',
            'Account Type',
            'Operation Type',
            'Order Number',
            'Amount',
            'Payment Method',
            'Status',
            'Date'
        ]);

        foreach ($settlements as $settlement) {
            // Determine Account Name & Type
            $accountName = 'N/A';
            $accountType = 'N/A';

            if ($settlement->maintenanceCompany) {
                $accountName = $settlement->maintenanceCompany->company_name_ar ?? $settlement->maintenanceCompany->company_name_en;
                $accountType = 'Company';
            } elseif ($settlement->user) {
                $accountName = $settlement->user->name;
                $accountType = 'Technician/User';
            }

            // Determine Operation Type
            $opType = 'N/A';
            if ($settlement->order) {
                $opType = $settlement->order->service_id ? 'Service' : 'Spare Parts';
            }

            fputcsv($handle, [
                $settlement->id,
                $accountName,
                $accountType,
                $opType,
                $settlement->order ? $settlement->order->order_number : 'N/A',
                $settlement->amount,
                $settlement->payment_method,
                $settlement->status,
                $settlement->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fseek($handle, 0);
        $csvData = stream_get_contents($handle);
        fclose($handle);

        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    private function filterQuery(Request $request)
    {
        $query = FinancialSettlement::with(['maintenanceCompany', 'user', 'order']);

        // Search (Order Number or Account Name)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Search by Order Number
                $q->whereHas('order', function ($sub) use ($search) {
                    $sub->where('order_number', 'like', "%{$search}%");
                })
                // Search by Maintenance Company Name
                ->orWhereHas('maintenanceCompany', function ($sub) use ($search) {
                     $sub->where('company_name_ar', 'like', "%{$search}%")
                         ->orWhere('company_name_en', 'like', "%{$search}%");
                })
                // Search by User Name
                ->orWhereHas('user', function ($sub) use ($search) {
                     $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Account Type Filter
        if ($request->has('account_type') && $request->account_type != 'all') {
            if ($request->account_type == 'company') {
                $query->whereNotNull('maintenance_company_id');
            } elseif ($request->account_type == 'technician' || $request->account_type == 'user') {
                $query->whereNotNull('user_id');
            }
        }

        // Operation Type Filter
        if ($request->has('operation_type') && $request->operation_type != 'all') {
            $opType = $request->operation_type;
            if ($opType == 'service') {
                $query->whereHas('order', function ($q) {
                    $q->whereNotNull('service_id');
                });
            } elseif ($opType == 'parts' || $opType == 'spare_parts') {
                $query->whereHas('order', function ($q) {
                    $q->whereNull('service_id');
                });
            }
        }

        // Payment Method Filter
        if ($request->has('payment_method') && $request->payment_method != 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Status Filter
        if ($request->has('status') && $request->status != 'all') {
             $query->where('status', $request->status);
        }

        return $query;
    }

    public function store(Request $request)
    {
        $settlement = FinancialSettlement::create($request->all());
        return response()->json(['status' => true, 'message' => 'Settlement created', 'data' => $settlement]);
    }

    public function show($id)
    {
        $settlement = FinancialSettlement::with(['maintenanceCompany', 'user', 'order'])->find($id);
        if (!$settlement) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Settlement retrieved', 'data' => $settlement]);
    }

    public function update(Request $request, $id)
    {
        $settlement = FinancialSettlement::find($id);
        if (!$settlement) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $request->validate([
            'status' => 'required|in:pending,transferred,suspended',
        ]);

        $settlement->update([
            'status' => $request->status
        ]);
        
        return response()->json(['status' => true, 'message' => 'Settlement status updated', 'data' => $settlement]);
    }

    public function destroy($id)
    {
        $settlement = FinancialSettlement::find($id);
        if (!$settlement) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $settlement->delete();
        return response()->json(['status' => true, 'message' => 'Settlement deleted']);
    }

    public function changeStatus(Request $request, $id)
{
    $settlement = FinancialSettlement::find($id);
    
    if (!$settlement) {
        return response()->json(['status' => false, 'message' => 'Settlement not found'], 404);
    }

    // Validate the incoming status
    $request->validate([
        'status' => 'required|in:pending,transferred,suspended',
    ]);

    // Update only the status
    $settlement->status = $request->status;
    $settlement->save();

    return response()->json([
        'status' => true,
        'message' => "Status successfully changed to {$request->status}",
        'data' => $settlement
    ]);
}
}
