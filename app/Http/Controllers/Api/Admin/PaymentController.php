<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        // 1. Statistics
        $stats = [
            'total_payments' => [
                'count' => Payment::count(),
                'sum' => Payment::sum('amount'),
            ],
            'completed_payments' => [
                'count' => Payment::where('status', 'completed')->count(),
                'sum' => Payment::where('status', 'completed')->sum('amount'),
            ],
            'pending_payments' => [ // Under Review
                'count' => Payment::where('status', 'pending')->count(),
                'sum' => Payment::where('status', 'pending')->sum('amount'),
            ],
            'rejected_payments' => [
                'count' => Payment::where('status', 'failed')->count(),
                'sum' => Payment::where('status', 'failed')->sum('amount'),
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
                    $query->whereHas('user', function ($q) {
                        $q->orderBy('name', 'asc');
                    });
                    break;
                default:
                    $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        // 4. Pagination
        $payments = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Payments retrieved successfully',
            'data' => [
                'stats' => $stats,
                'payments' => $payments
            ]
        ]);
    }

    public function download(Request $request)
    {
        $query = $this->filterQuery($request);
        $payments = $query->orderBy('id', 'desc')->get();

        $filename = "payments_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Headers
        fputcsv($handle, [
            'ID',
            'Client Name',
            'Client Type',
            'Transaction Type',
            'Amount',
            'Payment Method',
            'Status',
            'Date'
        ]);

        foreach ($payments as $payment) {
            // Determine Client Type
            $clientType = $payment->user ? $payment->user->type : 'N/A';
            
            // Determine Transaction Type
            $transType = 'N/A';
            if ($payment->order) {
                $transType = $payment->order->service_id ? 'Service' : 'Spare Parts';
            }

            fputcsv($handle, [
                $payment->id,
                $payment->user ? $payment->user->name : 'N/A',
                $clientType,
                $transType,
                $payment->amount,
                $payment->payment_method,
                $payment->status,
                $payment->created_at->format('Y-m-d H:i:s'),
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
        $query = Payment::with(['user', 'order']);

        // Search (User Name)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Client Type
        if ($request->has('client_type') && $request->client_type != 'all') {
            $type = $request->client_type;
            if ($type == 'company') $type = 'corporate_company';
            
            $query->whereHas('user', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

        // Transaction/Operation Type
        if ($request->has('transaction_type') && $request->transaction_type != 'all') {
            $transType = $request->transaction_type;
            if ($transType == 'service') {
                $query->whereHas('order', function ($q) {
                    $q->whereNotNull('service_id');
                });
            } elseif ($transType == 'spare_parts') {
                $query->whereHas('order', function ($q) {
                     $q->whereNull('service_id');
                });
            }
        }

        // Payment Method
        if ($request->has('payment_method') && $request->payment_method != 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        // Status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function show($id)
    {
        $payment = Payment::find($id);
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Payment retrieved', 'data' => $payment]);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $payment->update($request->all());
        return response()->json(['status' => true, 'message' => 'Payment updated', 'data' => $payment]);
    }

    public function destroy($id)
    {
        $payment = Payment::find($id);
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $payment->delete();
        return response()->json(['status' => true, 'message' => 'Payment deleted']);
    }
}
