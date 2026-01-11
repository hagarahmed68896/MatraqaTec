<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use \App\Traits\ValidatesOrderPhotos;

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $query = Order::with(['user', 'technician', 'service']);

        if ($user->type === 'technician') {
            // Find the technician profile for this user
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            if ($technician) {
                $query->where('technician_id', $technician->id);
            } else {
                return response()->json(['status' => true, 'message' => 'No assigned orders', 'data' => []]);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get();
        return response()->json(['status' => true, 'message' => 'Orders retrieved', 'data' => $orders]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->all();
        $data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $data['user_id'] = $user->id; 
        
        $order = Order::create($data);

        return response()->json(['status' => true, 'message' => 'Order created successfully', 'data' => $order]);
    }

    public function show($id)
    {
        $order = Order::with(['user', 'technician', 'service', 'attachments', 'reviews', 'payments', 'appointments'])
                    ->where('user_id', auth()->id())
                    ->where('id', $id)->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Order retrieved', 'data' => $order]);
    }

    public function update(Request $request, $id)
    {
        // Users can mostly only cancel or update limited fields. For now, general update but scoped.
        $order = Order::where('user_id', auth()->id())->where('id', $id)->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $order->update($request->all());

        return response()->json(['status' => true, 'message' => 'Order updated successfully', 'data' => $order]);
    }

    public function startWork(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        // Validate photo requirements
        $result = $this->validatePhotoCount($order, 'before');
        if ($result !== true) {
            return response()->json(['status' => false, 'message' => $result], 422);
        }

        $order->update(['status' => 'in_progress']);

        return response()->json(['status' => true, 'message' => 'Work started', 'data' => $order]);
    }

    public function finishWork(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        // Validate photo requirements
        $result = $this->validatePhotoCount($order, 'after');
        if ($result !== true) {
            return response()->json(['status' => false, 'message' => $result], 422);
        }

        $order->update(['status' => 'completed']);

        return response()->json(['status' => true, 'message' => 'Work finished', 'data' => $order]);
    }

    public function destroy($id)
    {
        // Users usually shouldn't hard delete orders, but providing 'cancel' logic in update or destroy.
        // Allowing destroy if ownership matches.
        $order = Order::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$order) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $order->delete();
        return response()->json(['status' => true, 'message' => 'Order deleted']);
    }
}
