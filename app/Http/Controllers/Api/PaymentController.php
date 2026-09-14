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
    use \App\Traits\HasAutoAssignment, \App\Traits\HandlesLocation;

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
            ->whereIn('status', ['completed', 'paid', 'success'])
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

                // Update Order Payment Method and clear sub_status
                $order->sub_status = null;
                $order->payment_method = $request->payment_method;
                $order->save();

                // Notify Technician that client has paid and he can now start the trip
                $technicianUserId = $order->technician->user_id ?? ($order->technician->user->id ?? null);
                if ($technicianUserId) {
                    $techUser = \App\Models\User::find($technicianUserId);
                    if ($techUser) {
                        $techUser->sendFcmNotification([
                            'type' => 'order_paid',
                            'title_ar' => 'تم دفع قيمة الخدمة',
                            'title_en' => 'Service Fee Paid',
                            'body_ar' => "قام العميل بسداد قيمة الخدمة للطلب رقم #{$order->order_number}. يمكنك الآن بدء الرحلة والتوجه للعميل.",
                            'body_en' => "Client has paid the service fee for order #{$order->order_number}. You can now start the trip to the client location.",
                            'data' => [
                                'type' => 'order_paid',
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'status' => $order->status,
                                'sub_status' => $order->sub_status,
                            ]
                        ], true);
                    }
                }

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

    /**
     * Verify payment status directly via API (Mobile App fallback)
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_id' => 'nullable|string',
        ]);

        $user = auth()->user();
        $order = Order::find($request->order_id);

        if (!$order || ($order->user_id !== $user->id && $user->type !== 'admin')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized or Order not found'], 403);
        }

        // 1. If already marked as paid in database
        if ($order->is_paid) {
            $order->sub_status = null;
            $order->save();
            return response()->json([
                'status' => true,
                'message' => 'الطلب مسدد بالفعل',
                'data' => $order->fresh(['payments', 'technician', 'service'])
            ]);
        }

        // 2. Extract payment ID from request or search via MyFatoorah
        $paymentId = $request->input('payment_id') 
            ?? $request->input('paymentId') 
            ?? $request->input('PaymentId') 
            ?? $request->input('Id');

        $myFatoorahService = app(\App\Services\MyFatoorahService::class);
        $paymentData = null;

        if ($paymentId) {
            $paymentData = $myFatoorahService->getPaymentStatus($paymentId);
        }

        if ($paymentData && in_array(strtolower($paymentData['InvoiceStatus'] ?? ''), ['paid', 'completed', 'success'])) {
            $paidAmount = (float) (
                $paymentData['InvoiceDisplayValue'] 
                ?? $paymentData['InvoiceTransactions'][0]['PaidCurrencyValue'] 
                ?? $paymentData['InvoiceTransactions'][0]['TransationValue'] 
                ?? $order->total_price
            );

            if ($paidAmount < ($order->total_price - 1.0) && $order->total_price > 0) {
                $paidAmount = (float) ($paymentData['InvoiceDisplayValue'] ?? $order->total_price);
            }

            // Record Payment in Database
            Payment::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'payment_method' => 'online',
                ],
                [
                    'user_id' => $order->user_id,
                    'amount' => $paidAmount,
                    'status' => 'completed',
                    'payment_date' => now(),
                    'transaction_id' => (string) $paymentId,
                ]
            );

            $order->sub_status = null;
            $order->payment_method = 'online';

            // If technician was not assigned yet, try auto-assignment now that payment is confirmed
            if (!$order->technician_id && $order->scheduled_at) {
                try {
                    $availableTech = $this->findAvailableTechnician(
                        $order->service_id,
                        $order->city_id,
                        \Carbon\Carbon::parse($order->scheduled_at),
                        $order->latitude,
                        $order->longitude
                    );

                    if ($availableTech) {
                        $order->technician_id = $availableTech->id;
                        $order->maintenance_company_id = $availableTech->maintenance_company_id;
                        $order->status = $availableTech->maintenance_company_id ? 'new' : 'scheduled';
                        $order->assigned_at = now();
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Auto-assignment on verify payment error: " . $e->getMessage());
                }
            }

            $order->save();

            // Send FCM Notifications safely
            try {
                if ($order->technician_id) {
                    $techUser = \App\Models\User::find($order->technician->user_id ?? $order->technician->user?->id);
                    if ($techUser) {
                        $techUser->sendFcmNotification([
                            'type' => 'order_paid',
                            'title_ar' => 'تم دفع قيمة الخدمة',
                            'title_en' => 'Service Fee Paid',
                            'body_ar' => "قام العميل بسداد قيمة الخدمة للطلب رقم #{$order->order_number}. يمكنك الآن بدء الرحلة والتوجه للعميل.",
                            'body_en' => "Client has paid the service fee for order #{$order->order_number}. You can now start the trip to the client location.",
                            'data' => [
                                'type' => 'order_paid',
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'status' => $order->status,
                                'sub_status' => $order->sub_status,
                            ]
                        ], true);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("FCM Notification error for tech: " . $e->getMessage());
            }

            return response()->json([
                'status' => true,
                'message' => 'تم التحقق من الدفع وتحديث حالة الطلب بنجاح',
                'data' => $order->fresh(['payments', 'technician', 'service'])
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'لم يتم التحقق من الدفع، حالة الفاتورة غير مكتملة أو معرّف الدفع غير صالح.',
            'debug_data' => $paymentData
        ], 400);
    }
}
