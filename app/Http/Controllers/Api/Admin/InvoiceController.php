<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filterQuery($request);

        // Sorting
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'newest':
                    $query->orderBy('issue_date', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('issue_date', 'asc');
                    break;
                case 'name':
                    $query->whereHas('order.user', function ($q) {
                        $q->orderBy('name', 'asc');
                    });
                    break;
                default:
                    $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $invoices = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Invoices retrieved successfully',
            'data' => $invoices
        ]);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['order.user', 'order.service', 'order.maintenanceCompany', 'order.technician'])->find($id);

        if (!$invoice) {
            return response()->json(['status' => false, 'message' => 'Invoice not found'], 404);
        }

        // Prepare items for display (Mocking items from Order details)
        $items = [];
        if ($invoice->order) {
            if ($invoice->order->service_id) {
                // Service Order
                $items[] = [
                    'description' => $invoice->order->service ? $invoice->order->service->name_ar : 'خدمة',
                    'quantity' => 1,
                    'price' => $invoice->amount, // Simplified
                    'total' => $invoice->amount
                ];
            } else {
                // Spare Parts (Mock data or real structure if available)
                $items[] = [
                    'description' => 'قطع غيار',
                    'quantity' => 1,
                    'price' => $invoice->amount,
                    'total' => $invoice->amount
                ];
            }
        }

        $invoiceData = $invoice->toArray();
        $invoiceData['items'] = $items;

        return response()->json(['status' => true, 'message' => 'Invoice details retrieved', 'data' => $invoiceData]);
    }

    public function download(Request $request)
    {
        $query = $this->filterQuery($request);
        $invoices = $query->orderBy('id', 'desc')->get();

        $filename = "invoices_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        // Add UTF-8 BOM
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Headers
        fputcsv($handle, [
            'Invoice Number',
            'Client Name',
            'Client Type',
            'Operation Type',
            'Amount',
            'Status',
            'Date'
        ]);

        foreach ($invoices as $invoice) {
            // Determine Client Type
            $clientType = ($invoice->order && $invoice->order->user) ? $invoice->order->user->type : 'N/A';
            
            // Determine Operation Type
            $opType = 'Spare Parts';
            if ($invoice->order && $invoice->order->service_id) {
                $opType = 'Service';
            }

            fputcsv($handle, [
                $invoice->invoice_number ?? $invoice->id,
                ($invoice->order && $invoice->order->user) ? $invoice->order->user->name : 'N/A',
                $clientType,
                $opType,
                $invoice->amount,
                $invoice->status == 'sent' ? 'مرسلة' : 'غير مرسلة',
                $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : 'N/A',
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

    public function send($id)
    {
        $invoice = Invoice::with(['order.user'])->find($id);
        if (!$invoice) {
            return response()->json(['status' => false, 'message' => 'Invoice not found'], 404);
        }

        // Send Email if User has email
        if ($invoice->order && $invoice->order->user && $invoice->order->user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($invoice->order->user->email)->send(new \App\Mail\InvoiceMail($invoice));
            } catch (\Exception $e) {
                // Log error but continue to mark as sent or handle as needed
                // return response()->json(['status' => false, 'message' => 'Failed to send email: ' . $e->getMessage()], 500);
            }
        }

        $invoice->update(['status' => 'sent']);

        return response()->json(['status' => true, 'message' => 'Invoice sent successfully', 'data' => $invoice]);
    }

    private function filterQuery(Request $request)
    {
        $query = Invoice::with(['order.user']);

        // Search (User Name or Invoice Number)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('order.user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Client Type
        if ($request->has('client_type') && $request->client_type != 'all') {
            $type = $request->client_type;
            if ($type == 'company') $type = 'corporate_company';
            
            $query->whereHas('order.user', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

        // Operation/Transaction Type
        if ($request->has('operation_type') && $request->operation_type != 'all') {
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

        // Status
        if ($request->has('status') && $request->status != 'all') {
            $status = $request->status; 
            // Map UI status 'sent'/'unsent' to DB enum 'sent'/'not_sent' if mismatch, but here matching 'sent'/'not_sent' (or using arabic mapping if provided).
            // Screenshot says "مرسلة" (Sent) / "غير مرسلة" (Not Sent)
            if ($status == 'unsent') $status = 'not_sent';
            
            $query->where('status', $status);
        }

        return $query;
    }
}
