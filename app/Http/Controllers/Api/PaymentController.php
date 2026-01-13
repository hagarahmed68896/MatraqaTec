<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['user', 'order'])
            ->where('user_id', auth()->id())
            ->get();
        return response()->json(['status' => true, 'message' => 'Payments retrieved', 'data' => $payments]);
    }

    public function store(Request $request)
    {
        $request->merge(['user_id' => auth()->id()]);
        $payment = Payment::create($request->all());
        return response()->json(['status' => true, 'message' => 'Payment recorded', 'data' => $payment]);
    }

    public function payOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:credit_card,apple_pay,wallet',
        ]);

        $user = auth()->user();
        $order = Order::find($request->order_id);

        if ($order->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($order->total_price <= 0) {
            return response()->json(['status' => false, 'message' => 'Invalid order amount'], 422);
        }

        return DB::transaction(function () use ($user, $order, $request) {
            // 1. Handle Wallet Deduction
            if ($request->payment_method === 'wallet') {
                if ($user->wallet_balance < $order->total_price) {
                    return response()->json(['status' => false, 'message' => 'Insufficient wallet balance'], 422);
                }

                $user->wallet_balance -= $order->total_price;
                $user->save();

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => -$order->total_price,
                    'type' => 'payment',
                    'note' => 'Payment for Order #' . $order->order_number,
                    'reference_id' => $order->id,
                    'reference_type' => Order::class,
                ]);
            }

            // 2. Record Payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'payment_date' => now(),
            ]);

            // 3. Update Order Status (optional, depending on flow)
            // $order->status = 'paid'; // If you have a 'paid' status
            // $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Payment successful',
                'data' => [
                    'payment' => $payment,
                    'order_id' => $order->id,
                    'new_wallet_balance' => $user->wallet_balance
                ]
            ]);
        });
    }

    public function show($id)
    {
        $payment = Payment::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Payment retrieved', 'data' => $payment]);
    }
    
    // Update/Destroy removed (Admin/System managed)
}
