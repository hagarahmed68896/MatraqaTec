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
        $query = Order::with(['user', 'technician.user', 'maintenanceCompany.user', 'service']);

        // Filter by tab/status
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

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->has('service_category')) {
            $query->whereHas('service', function($q) use ($request) {
                $q->where('category', $request->service_category);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

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
            'status' => 'scheduled', // Or 'accepted' depending on your workflow
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
