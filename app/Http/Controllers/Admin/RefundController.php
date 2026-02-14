<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $query = Refund::with(['order.user', 'order.maintenanceCompany']);

        // Filter by Status: 'pending' (Requests) vs others (History)
        if ($request->has('status') && $request->status != 'all') {
            if ($request->status == 'history') {
                 $query->whereIn('status', ['transferred', 'rejected']);
            } else {
                 $query->where('status', $request->status);
            }
        }

        // Search by Order Number or Client Name
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($sub) use ($search) {
                        $sub->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($u) use ($search) {
                                $u->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('maintenanceCompany', function ($c) use ($search) {
                                $c->where('company_name_ar', 'like', "%{$search}%")
                                  ->orWhere('company_name_en', 'like', "%{$search}%");
                            });
                  });
            });
        }

        // Filter by Client Type
        if ($request->has('client_type') && $request->client_type != 'all') {
            $clientType = $request->client_type;
             $query->whereHas('order.user', function ($q) use ($clientType) {
                 if ($clientType == 'individual') {
                     $q->where('type', 'individual');
                 } elseif ($clientType == 'company') {
                     $q->where('type', 'maintenance_company'); 
                 }
            });
        }

        // Sort
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('limit', 10);
        $items = $query->paginate($perPage);

        return view('admin.refunds.index', compact('items'));
    }

    public function show($id)
    {
        $item = Refund::with(['order.user', 'order.maintenanceCompany', 'order.service'])->findOrFail($id);
        return view('admin.refunds.show', compact('item'));
    }

    public function changeStatus(Request $request, $id)
    {
        $refund = Refund::with(['order.user'])->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,transferred,rejected',
            'reason' => 'nullable|string',
            'admin_note' => 'nullable|string'
        ]);

        $oldStatus = $refund->status;
        $refund->status = $request->status;
        if ($request->has('reason')) $refund->reason = $request->reason;
        if ($request->has('admin_note')) $refund->admin_note = $request->admin_note;

        return DB::transaction(function () use ($refund, $oldStatus, $request) {
            // If status is changed to 'transferred' and it wasn't already transferred
            if ($refund->status === 'transferred' && $oldStatus !== 'transferred') {
                $user = $refund->order->user;
                if ($user) {
                    $user->wallet_balance += $refund->amount;
                    $user->save();

                    \App\Models\WalletTransaction::create([
                        'user_id' => $user->id,
                        'amount' => $refund->amount,
                        'type' => 'refund',
                        'note' => 'Administrative Refund APPROVED for Order #' . ($refund->order->order_number ?? $refund->order_id),
                        'reference_id' => $refund->id,
                        'reference_type' => Refund::class,
                    ]);
                }
            }

            $refund->save();
            return back()->with('success', __('Refund status updated successfully.'));
        });
    }

    public function export(Request $request)
    {
        $query = Refund::with(['order.user', 'order.maintenanceCompany']);

        if ($request->has('status') && $request->status != 'all') {
            if ($request->status == 'history') {
                 $query->whereIn('status', ['transferred', 'rejected']);
            } else {
                 $query->where('status', $request->status);
            }
        }
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($sub) use ($search) {
                        $sub->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($u) use ($search) {
                                $u->where('name', 'like', "%{$search}%");
                            })
                             ->orWhereHas('maintenanceCompany', function ($c) use ($search) {
                                $c->where('company_name_ar', 'like', "%{$search}%")
                                  ->orWhere('company_name_en', 'like', "%{$search}%");
                            });
                  });
            });
        }

        if ($request->has('client_type') && $request->client_type != 'all') {
            $clientType = $request->client_type;
             $query->whereHas('order.user', function ($q) use ($clientType) {
                 if ($clientType == 'individual') {
                     $q->where('type', 'individual');
                 } elseif ($clientType == 'company') {
                     $q->where('type', 'maintenance_company');
                 }
            });
        }
        
        $refunds = $query->orderBy('created_at', 'desc')->get();

        $filename = "refunds_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

        fputcsv($handle, [
            'Refund Number',
            'Operation Number',
            'Client Name',
            'Client Type',
            'Order Number',
            'Amount',
            'Status',
            'Date'
        ]);

        foreach ($refunds as $refund) {
            $clientName = 'N/A';
            $clientType = 'N/A';
            
            if ($refund->order) {
                 if ($refund->order->maintenanceCompany) {
                    $clientName = $refund->order->maintenanceCompany->company_name_ar ?? $refund->order->maintenanceCompany->company_name_en;
                    $clientType = 'Maintenance Company';
                 } elseif ($refund->order->user) {
                    $clientName = $refund->order->user->name;
                    $clientType = $refund->order->user->type;
                 }
            }

            fputcsv($handle, [
                $refund->refund_number,
                $refund->id,
                $clientName,
                $clientType,
                $refund->order ? $refund->order->order_number : 'N/A',
                $refund->amount,
                $refund->status,
                $refund->created_at->format('Y-m-d'),
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
}
