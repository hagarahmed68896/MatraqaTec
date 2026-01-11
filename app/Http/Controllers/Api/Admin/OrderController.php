<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'technician.user', 'maintenanceCompany.user', 'service.parent']);

        // 1. Filter by tab/status
        if ($request->has('tab')) {
            switch ($request->tab) {
                case 'new':
                    $query->where('status', 'new');
                    break;
                case 'scheduled':
                    $query->where('status', 'scheduled');
                    break;
                case 'in_progress':
                    $query->where('status', 'in_progress');
                    break;
                case 'completed':
                    $query->where('status', 'completed');
                    break;
                case 'rejected':
                    $query->where('status', 'rejected');
                    break;
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // 2. Search Logic
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // 3. NEW: Filter by Customer Type
        if ($request->has('customer_type')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('type', $request->customer_type);
            });
        }

        // 4. NEW: Filter by Service Category (Parent)
        if ($request->has('service_category_id')) {
            $query->whereHas('service', function ($q) use ($request) {
                $q->where('parent_id', $request->service_category_id);
            });
        }

        // 5. NEW: Filter by Specific Service IDs (Child Services)
        if ($request->has('service_ids')) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereIn('service_id', $serviceIds);
        }

        // 6. NEW: Filter by Technician Type (Platform vs Company)
        if ($request->has('technician_type')) {
            if ($request->technician_type === 'platform') {
                $query->whereNotNull('technician_id')->whereNull('maintenance_company_id');
            } elseif ($request->technician_type === 'company') {
                $query->whereNotNull('maintenance_company_id');
            }
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        // 7. Enhanced Sorting
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'name':
                    $query->join('users', 'orders.user_id', '=', 'users.id')
                          ->orderBy('users.name', 'asc')
                          ->select('orders.*');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(10);

        // Stats for the dashboard
        $stats = [
            'total' => Order::count(),
            'scheduled' => Order::where('status', 'scheduled')->count(),
            'in_progress' => Order::where('status', 'in_progress')->count(),
            'completed' => Order::where('status', 'completed')->count(),
        ];

        return response()->json([
            'status' => true, 
            'message' => 'Orders retrieved', 
            'stats' => $stats,
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'technician.user', 'maintenanceCompany.user', 'service', 'attachments', 'reviews', 'payments', 'appointments'])->find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Order retrieved', 'data' => $order]);
    }

    public function accept(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $request->validate([
            'technician_id' => 'nullable|exists:technicians,id',
            'maintenance_company_id' => 'nullable|exists:maintenance_companies,id',
            'scheduled_at' => 'nullable|date',
        ]);

        if (!$request->technician_id && !$request->maintenance_company_id) {
            return response()->json(['status' => false, 'message' => 'Please select a technician or a maintenance company'], 422);
        }

        $order->update([
            'technician_id' => $request->technician_id,
            'maintenance_company_id' => $request->maintenance_company_id,
            'status' => 'scheduled',
            'scheduled_at' => $request->scheduled_at ?? $order->scheduled_at,
        ]);

        return response()->json(['status' => true, 'message' => 'Order accepted and assigned successfully', 'data' => $order]);
    }

    public function refuse(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $order->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json(['status' => true, 'message' => 'Order refused successfully', 'data' => $order]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $order->update($request->all());

        return response()->json(['status' => true, 'message' => 'Order updated successfully', 'data' => $order]);
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $order->delete();
        return response()->json(['status' => true, 'message' => 'Order deleted']);
    }
}
