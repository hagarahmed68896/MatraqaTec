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
                    $query->orderBy('invoices.issue_date', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('invoices.issue_date', 'asc');
                    break;
                case 'name':
                    $query->orderBy('users.name', 'asc');
                    break;
                default:
                    $query->orderBy('invoices.id', 'desc');
            }
        } else {
            $query->orderBy('invoices.id', 'desc');
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
            $serviceName = $item->order->service 
                ? (app()->getLocale() == 'ar' ? $item->order->service->name_ar : $item->order->service->name_en) 
                : __('Service');

            $items_details[] = [
                'description' => $serviceName,
                'quantity' => 1,
                'price' => $item->amount,
                'total' => $item->amount
            ];
        }

        return view('admin.invoices.show', compact('item', 'items_details'));
    }

    public function download(Request $request)
    {
        $query = $this->filterQuery($request);
        
        // If specific IDs are provided, limit to those
        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query = Invoice::whereIn('id', $ids)->with(['order.user']);
        }

        $invoices = $query->orderBy('id', 'desc')->get();

        $filename = "invoices_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($handle, [
            __('Invoice Number'),
            __('Customer Name'),
            __('Client Type'),
            __('Operation Type'),
            __('Amount'),
            __('Status'),
            __('Date')
        ]);

        foreach ($invoices as $invoice) {
                $clientType = ($invoice->order && $invoice->order->user) ? __($invoice->order->user->type) : 'N/A';
                $service = ($invoice->order && $invoice->order->service) ? $invoice->order->service : null;
                $opType = $service 
                    ? (app()->getLocale() == 'ar' ? $service->name_ar : $service->name_en) 
                    : __('Spare Parts');

                fputcsv($handle, [
                    $invoice->invoice_number ?? $invoice->id,
                    ($invoice->order && $invoice->order->user) ? $invoice->order->user->name : 'N/A',
                    $clientType,
                    $opType,
                    $invoice->amount,
                    $invoice->status == 'sent' ? __('Sent') : __('Not Sent'),
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
        $query = Invoice::select('invoices.*')
            ->join('orders', 'invoices.order_id', '=', 'orders.id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->with(['order.user']);

        // Bulk IDs
        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query->whereIn('invoices.id', $ids);
            return $query;
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoices.invoice_number', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        // Client Type
        if ($request->filled('client_type') && $request->client_type != 'all') {
            $type = $request->client_type;
            if ($type == 'company') $type = 'maintenance_company';
            $query->where('users.type', $type);
        }

        // Operation Type
        if ($request->filled('operation_type') && $request->operation_type != 'all') {
            if ($request->operation_type == 'service') {
                $query->whereNotNull('orders.service_id');
            } else {
                $query->whereNull('orders.service_id');
            }
        }

        // Status
        if ($request->filled('status') && $request->status != 'all') {
            $status = $request->status;
            if ($status == 'unsent') $status = 'not_sent';
            $query->where('invoices.status', $status);
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
