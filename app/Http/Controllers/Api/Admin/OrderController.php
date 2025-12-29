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
        $query = Order::with(['user', 'technician', 'service']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['status' => true, 'message' => 'Orders retrieved', 'data' => $orders]);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'technician', 'service', 'attachments', 'reviews', 'payments', 'appointments'])->find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Order retrieved', 'data' => $order]);
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
