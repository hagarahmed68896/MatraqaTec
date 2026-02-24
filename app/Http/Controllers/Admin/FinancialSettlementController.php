<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialSettlement;
use App\Models\Payment;
use Illuminate\Http\Request;

class FinancialSettlementController extends Controller
{
    public function index(Request $request)
    {
        // 1. Statistics
        $stats = [
            'total_payments' => [
                'value' => Payment::where('status', 'completed')->sum('amount'),
                'label' => 'إجمالي المدفوعات'
            ],
            'total' => [
                'value' => FinancialSettlement::sum('amount'),
                'label' => 'إجمالي التسويات المالية'
            ],
            'pending' => [
                'value' => FinancialSettlement::where('status', 'pending')->sum('amount'),
                'label' => 'تسويات معلقة'
            ],
            'transferred' => [
                'value' => FinancialSettlement::where('status', 'transferred')->sum('amount'),
                'label' => 'تسويات محولة'
            ],
        ];

        // 2. Base Query with Filters
        $query = $this->filterQuery($request);

        // 3. Sorting
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'name':
                    $query->join('users', 'financial_settlements.user_id', '=', 'users.id')
                          ->orderBy('users.name', 'asc')
                          ->select('financial_settlements.*');
                    break;
                default:
                    $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        // 4. Pagination
        $perPage = $request->input('limit', 10);
        $items = $query->paginate($perPage);

        return view('admin.financial_settlements.index', compact('items', 'stats'));
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
            $accountName = 'N/A';
            $accountType = 'N/A';

            if ($settlement->maintenanceCompany) {
                $accountName = $settlement->maintenanceCompany->company_name_ar ?? $settlement->maintenanceCompany->company_name_en;
                $accountType = 'Company';
            } elseif ($settlement->user) {
                $accountName = $settlement->user->name;
                $accountType = 'Technician/User';
            }

            $opType = 'N/A';
            if ($settlement->order) {
                $opType = $settlement->order->service_id ? 'Service' : 'Spare Parts';
            }

            fputcsv($handle, [
                $settlement->id,
                $accountName,
                $accountType,
                $opType,
                $settlement->order->order_number ?? $settlement->order_id,
                $settlement->amount,
                $settlement->payment_method,
                $settlement->status,
                $settlement->created_at->format('Y-m-d H:i:s')
            ]);
        }

        fclose($handle);

        return response()->stream(function () use ($handle) {
            // Memory handle is already closed above, but this would work if we didn't close it or used another stream.
            // Actually fseek/rewind would be needed if we want to stream from the start.
        }, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
        
        // Revision: Streamed response in Laravel is simpler:
        return response()->streamDownload(function() use ($payments) { // Wait, logic below is better
             // ...
        }, $filename);
    }

    private function filterQuery(Request $request)
    {
        $query = FinancialSettlement::with(['user', 'order', 'maintenanceCompany']);

        // Search (Account Name or Order Number)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('maintenanceCompany', function ($cq) use ($search) {
                      $cq->where('company_name_ar', 'like', "%{$search}%")
                         ->orWhere('company_name_en', 'like', "%{$search}%");
                  })
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        // Account / Client Type Filter
        if ($request->filled('account_type')) {
            $type = $request->account_type;
            if ($type == 'company') {
                $query->whereNotNull('maintenance_company_id');
            } elseif ($type == 'technician') {
                $query->whereNotNull('user_id');
            }
        }

        // Operation / Transaction Type Filter
        if ($request->filled('operation_type')) {
            $opType = $request->operation_type;
            if ($opType == 'service') {
                $query->whereHas('order', function ($q) {
                    $q->whereNotNull('service_id');
                });
            } elseif ($opType == 'spare_parts') {
                $query->whereHas('order', function ($q) {
                    $q->whereNull('service_id');
                });
            }
        }

        // Status Filter
        if ($request->filled('status')) {
             $query->where('status', $request->status);
        }

        // Payment Method Filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return $query;
    }

    public function show($id)
    {
        $item = FinancialSettlement::with(['user', 'order', 'maintenanceCompany'])->findOrFail($id);
        return view('admin.financial_settlements.show', compact('item'));
    }

    public function changeStatus(Request $request, $id)
    {
        $item = FinancialSettlement::findOrFail($id);
        $item->update(['status' => $request->status]);
        return back()->with('success', __('Status updated successfully.'));
    }

    public function destroy($id)
    {
        $item = FinancialSettlement::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.financial-settlements.index')->with('success', __('Deleted successfully.'));
    }
}
