<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MyFatoorahService;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class MyFatoorahCallbackController extends Controller
{
    use \App\Traits\HasAutoAssignment, \App\Traits\HandlesLocation;

    protected $myFatoorahService;

    public function __construct()
    {
        // Manual instantiation to avoid DI issues on some production environments
        $this->myFatoorahService = app(\App\Services\MyFatoorahService::class);
    }

    /**
     * Handle MyFatoorah Success Callback & Webhooks
     */
    public function success(Request $request)
    {
        // Extract paymentId flexibly from various casing / Webhook formats
        $paymentId = $request->input('paymentId') 
            ?? $request->input('PaymentId') 
            ?? $request->input('Id') 
            ?? $request->input('payment_id') 
            ?? $request->input('Data.PaymentId') 
            ?? $request->input('Data.InvoiceId')
            ?? $request->query('paymentId')
            ?? $request->query('PaymentId')
            ?? $request->query('Id');

        if (!$paymentId) {
            Log::warning("MyFatoorah Callback missing payment ID. Request data: ", $request->all());
            if ($request->wantsJson()) {
                return response()->json(['status' => false, 'message' => 'Payment ID is missing'], 400);
            }
            return redirect(config('myfatoorah.mobile_error_url') . '?error=Payment ID is missing');
        }

        $paymentData = $this->myFatoorahService->getPaymentStatus($paymentId);

        if ($paymentData && in_array(strtolower($paymentData['InvoiceStatus'] ?? ''), ['paid', 'completed', 'success'])) {
            
            // Extract the original Order ID from CustomerReference or UserDefinedField
            $orderId = (int) ($paymentData['CustomerReference'] ?? $paymentData['UserDefinedField'] ?? 0);
            if (!$orderId && $request->filled('order_id')) {
                $orderId = (int) $request->input('order_id');
            }

            $order = Order::find($orderId);

            if ($order) {
                // Extract paid amount in SAR (Handle multi-currency: InvoiceDisplayValue vs InvoiceValue)
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
                        'transaction_id' => (string) ($paymentId ?? ($paymentData['InvoiceTransactions'][0]['PaymentId'] ?? null)),
                    ]
                );

                // Update Order Payment Method and clear sub_status
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
                        Log::warning("Auto-assignment on payment error: " . $e->getMessage());
                    }
                }

                $order->save();

                // Notify Technician that client has paid and he can now start the trip
                try {
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
                } catch (\Exception $e) {
                    Log::warning("Technician payment notification failed: " . $e->getMessage());
                }

                // Notify Client of successful payment
                try {
                    $clientUser = \App\Models\User::find($order->user_id);
                    if ($clientUser) {
                        $clientUser->sendFcmNotification([
                            'type' => 'payment_successful',
                            'title_ar' => 'تم سداد قيمة الخدمة بنجاح',
                            'title_en' => 'Payment Successful',
                            'body_ar' => "تم سداد قيمة الخدمة للطلب رقم #{$order->order_number} بنجاح. الفني سيتوجه إليكم قريباً.",
                            'body_en' => "Payment for order #{$order->order_number} was successful. The technician will head to your location shortly.",
                            'data' => [
                                'type' => 'payment_successful',
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                            ]
                        ], true);
                    }
                } catch (\Exception $e) {
                    Log::warning("Client payment notification failed: " . $e->getMessage());
                }

                Log::info("MyFatoorah Payment Success for Order: {$order->id}");
                
                if ($request->wantsJson() || ($request->is('api/*') && $request->header('Accept') === 'application/json')) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Payment confirmed successfully',
                        'data' => $order->fresh(['payments', 'technician', 'service'])
                    ]);
                }

                // --- SUCCESS REDIRECT ---
                return redirect(config('myfatoorah.mobile_success_url'));
            }
        }

        // --- FAILURE REDIRECT ---
        Log::error("MyFatoorah Payment Verification Failed: " . json_encode($paymentData));
        
        $errorMessage = "Invoice status: " . ($paymentData['InvoiceStatus'] ?? 'Unknown');
        $detail = $paymentData['InvoiceTransactions'][0]['Error'] ?? 'Unknown error';
        
        if ($request->wantsJson()) {
            return response()->json([
                'status' => false,
                'message' => $errorMessage . ' | ' . $detail,
                'debug_data' => $paymentData
            ], 400);
        }

        return redirect(config('myfatoorah.mobile_error_url') . '?error=' . urlencode($errorMessage . ' | ' . $detail));
    }

    /**
     * Handle MyFatoorah Error Callback (User Cancelled or Direct Error)
     */
    public function error(Request $request)
    {
        $paymentId = $request->input('paymentId') 
            ?? $request->input('PaymentId') 
            ?? $request->input('Id') 
            ?? $request->query('paymentId')
            ?? $request->query('PaymentId')
            ?? $request->query('Id');

        $errorMessage = 'Payment failed or was canceled by the user.';
        
        if ($paymentId) {
            $paymentData = $this->myFatoorahService->getPaymentStatus($paymentId);
            if (isset($paymentData['InvoiceStatus'])) {
                $errorMessage = "Invoice status: " . $paymentData['InvoiceStatus'];
                if (isset($paymentData['InvoiceTransactions'][0]['Error'])) {
                    $errorMessage .= " | Detail: " . $paymentData['InvoiceTransactions'][0]['Error'];
                }
            }
        }

        Log::error("MyFatoorah Payment Error Callback: ", $request->all());
        
        if ($request->wantsJson()) {
            return response()->json(['status' => false, 'message' => $errorMessage], 400);
        }

        return redirect(config('myfatoorah.mobile_error_url') . '?error=' . urlencode($errorMessage));
    }
}
