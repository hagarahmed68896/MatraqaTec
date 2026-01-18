<?php

namespace App\Http\Controllers\Api\Admin;

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
                 // Map frontend filters to database values if needed, or assume direct match
                 // 'individual' -> type 'individual'
                 // 'company' -> type 'corporate_company' or 'maintenance_company' ? 
                 // User model comment says: // admin, individual, corporate_company, technician, maintenance_company
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
                case 'name':
                     // Sorting by relation is complex, fallback to newest
                     $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->input('limit', 10);
        $refunds = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Refunds retrieved successfully',
            'data' => $refunds
        ]);
    }

    public function show($id)
    {
        $refund = Refund::with(['order.user', 'order.maintenanceCompany', 'order.service'])->find($id);

        if (!$refund) {
            return response()->json(['status' => false, 'message' => 'Refund not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Refund details retrieved',
            'data' => $refund
        ]);
    }

    public function changeStatus(Request $request, $id)
    {
        $refund = Refund::find($id);

        if (!$refund) {
            return response()->json(['status' => false, 'message' => 'Refund not found'], 404);
        }

        $request->validate([
            'status' => 'required|in:pending,transferred,rejected',
            'reason' => 'nullable|string', // Rejection reason
            'admin_note' => 'nullable|string'
        ]);

        $refund->status = $request->status;
        if ($request->has('reason')) $refund->reason = $request->reason;
        if ($request->has('admin_note')) $refund->admin_note = $request->admin_note;
        $refund->save();

        return response()->json([
            'status' => true,
            'message' => 'Refund status updated',
            'data' => $refund
        ]);
    }

    public function export(Request $request)
    {
        // Re-use filter logic
        $query = Refund::with(['order.user', 'order.maintenanceCompany']);

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
        
        $refunds = $query->orderBy('created_at', 'desc')->get();

        $filename = "refunds_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        // Add UTF-8 BOM
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Headers based on requirements
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
                     // Translate type if needed
                    $clientType = $refund->order->user->type;
                 }
            }

            fputcsv($handle, [
                $refund->refund_number,
                $refund->id, // Operation number or reuse refund number
                $clientName,
                $clientType,
                $refund->order ? $refund->order->order_number : 'N/A',
                $refund->amount,
                $refund->status,
                $refund->created_at->format('Y-m-d'),
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
}
