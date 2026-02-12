<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

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

        $items = $query->paginate(10);

        return view('admin.invoices.index', compact('items'));
    }

    public function show($id)
    {
        $item = Invoice::with(['order.user', 'order.service', 'order.maintenanceCompany', 'order.technician'])->findOrFail($id);

        // Prepare items for display
        $items_details = [];
        if ($item->order) {
            if ($item->order->service_id) {
                $items_details[] = [
                    'description' => $item->order->service ? $item->order->service->name_ar : 'خدمة',
                    'quantity' => 1,
                    'price' => $item->amount,
                    'total' => $item->amount
                ];
            } else {
                $items_details[] = [
                    'description' => 'قطع غيار',
                    'quantity' => 1,
                    'price' => $item->amount,
                    'total' => $item->amount
                ];
            }
        }

        return view('admin.invoices.show', compact('item', 'items_details'));
    }

    public function download(Request $request)
    {
        $query = $this->filterQuery($request);
        $invoices = $query->orderBy('id', 'desc')->get();

        $filename = "invoices_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

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
            $clientType = ($invoice->order && $invoice->order->user) ? $invoice->order->user->type : 'N/A';
            $opType = ($invoice->order && $invoice->order->service_id) ? 'Service' : 'Spare Parts';

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
        
        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]
        );
    }

    public function send($id)
    {
        $invoice = Invoice::with(['order.user'])->findOrFail($id);

        if ($invoice->order && $invoice->order->user && $invoice->order->user->email) {
            try {
                Mail::to($invoice->order->user->email)->send(new InvoiceMail($invoice));
            } catch (\Exception $e) {
                // Log error
            }
        }

        $invoice->update(['status' => 'sent']);

        return back()->with('success', __('Invoice sent successfully.'));
    }

    private function filterQuery(Request $request)
    {
        $query = Invoice::with(['order.user']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('order.user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('client_type') && $request->client_type != 'all') {
            $type = $request->client_type;
            if ($type == 'company') $type = 'maintenance_company';
            
            $query->whereHas('order.user', function ($q) use ($type) {
                $q->where('type', $type);
            });
        }

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

        if ($request->has('status') && $request->status != 'all') {
            $status = $request->status; 
            if ($status == 'unsent') $status = 'not_sent';
            $query->where('status', $status);
        }

        return $query;
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return redirect()->route('admin.invoices.index')->with('success', __('Invoice deleted successfully.'));
    }
}
