<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MyFatoorahService;

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

        if (!$order || $order->user_id !== $user->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized or Order not found'], 403);
        }

        // --- Check if Order is fully paid ---
        $totalPaid = Payment::where('order_id', $order->id)
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalPaid >= $order->total_price && $order->total_price > 0) {
            return response()->json([
                'status' => false,
                'message' => 'هذا الطلب تم سداده بالكامل بالفعل مسبقاً.'
            ], 422);
        }

        // Calculate if there's a remaining balance (useful for partial payments like visit then parts)
        $remainingAmount = $order->total_price - $totalPaid;

        // --- MyFatoorah Online Payment ---
        if (in_array($request->payment_method, ['credit_card', 'apple_pay'])) {
            try {
                // 1. Prepare Mobile (Strict 11-char limit)
                $cleanMobile = preg_replace('/[^0-9]/', '', $user->phone);
                if (strlen($cleanMobile) > 11) {
                    $cleanMobile = substr($cleanMobile, -10);
                }

                $isSandbox = config('myfatoorah.is_sandbox', false);
                $baseUrl = $isSandbox ? 'https://apitest.myfatoorah.com' : 'https://api-sa.myfatoorah.com';
                $apiToken = config('myfatoorah.api_token');

                $payload = [
                    'NotificationOption' => 'Lnk',
                    'InvoiceValue'       => $remainingAmount,
                    'CustomerName'       => $user->name ?: 'Customer',
                    'DisplayCurrencyIso' => 'SAR',
                    'CustomerEmail'      => $user->email ?: 'customer@example.com',
                    'CustomerMobile'     => $cleanMobile,
                    'CallBackUrl'        => config('myfatoorah.callback_url'),
                    'ErrorUrl'           => config('myfatoorah.error_url'),
                    'Language'           => 'ar',
                    'CustomerReference'  => $order->id,
                ];

                $response = \Illuminate\Support\Facades\Http::withToken($apiToken)
                    ->post("$baseUrl/v2/SendPayment", $payload);

                if ($response->successful() && $response->json('IsSuccess')) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Invoice created successfully',
                        'data' => [
                            'payment_url' => $response->json('Data.InvoiceURL'),
                            'invoice_id' => $response->json('Data.InvoiceId'),
                            'order_id' => $order->id
                        ]
                    ]);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to generate payment invoice.',
                    'debug_error' => $response->json()
                ], 500);

            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => 'Payment Initiation Error: ' . $e->getMessage()], 500);
            }
        }

        // --- Wallet Deduction ---
        return DB::transaction(function () use ($user, $order, $request, $remainingAmount) {
            if ($request->payment_method === 'wallet') {
                if ($user->wallet_balance < $remainingAmount) {
                    return response()->json(['status' => false, 'message' => 'Insufficient wallet balance'], 422);
                }

                $user->wallet_balance -= $remainingAmount;
                $user->save();

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => -$remainingAmount,
                    'type' => 'payment',
                    'note' => 'Payment for Order #' . $order->order_number . ' (Balance)',
                    'reference_id' => $order->id,
                    'reference_type' => Order::class,
                ]);

                // Record Payment
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'amount' => $remainingAmount,
                    'payment_method' => $request->payment_method,
                    'status' => 'completed',
                    'payment_date' => now(),
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment successful via wallet',
                    'data' => [
                        'payment' => $payment,
                        'order_id' => $order->id,
                        'new_wallet_balance' => $user->wallet_balance,
                        'paid_amount' => $remainingAmount
                    ]
                ]);
            }
        });
    }

    public function show($id)
    {
        $payment = Payment::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Payment retrieved', 'data' => $payment]);
    }
    
}
