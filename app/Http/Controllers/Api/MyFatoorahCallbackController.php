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
    protected $myFatoorahService;

    public function __construct()
    {
        // Manual instantiation to avoid DI issues on some production environments
        $this->myFatoorahService = app(\App\Services\MyFatoorahService::class);
    }

    /**
     * Handle MyFatoorah Success Callback
     */
    public function success(Request $request)
    {
        $paymentId = $request->paymentId;

        if (!$paymentId) {
            return redirect(config('myfatoorah.mobile_error_url') . '?error=Payment ID is missing');
        }

        $paymentData = $this->myFatoorahService->getPaymentStatus($paymentId);

        if ($paymentData && $paymentData['InvoiceStatus'] === 'Paid') {
            
            // Extract the original Order ID from CustomerReference
            $orderId = (int) $paymentData['CustomerReference'];
            $order = Order::find($orderId);

            if ($order) {
                // Determine if this is a spare parts payment or general order payment
                // For now, based on instructions, we assume any payment here covers the order/spare parts
                
                // Record Payment in Database
                Payment::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'payment_method' => 'online', // or credit_card based on gateway mapping
                        'amount' => $paymentData['InvoiceValue']
                    ],
                    [
                        'user_id' => $order->user_id,
                        'status' => 'completed',
                        'payment_date' => now(),
                    ]
                );

                // Option: Update order status to paid or anything specific
                // $order->update(['is_paid' => true]);

                Log::info("MyFatoorah Payment Success for Order: {$order->id}");
                
                // --- SUCCESS REDIRECT ---
                return redirect(config('myfatoorah.mobile_success_url'));
            }
        }

        // --- FAILURE REDIRECT ---
        Log::error("MyFatoorah Payment Verification Failed: " . json_encode($paymentData));
        
        $errorMessage = "Invoice status: " . ($paymentData['InvoiceStatus'] ?? 'Unknown');
        $detail = $paymentData['InvoiceTransactions'][0]['Error'] ?? 'Unknown error';
        
        return redirect(config('myfatoorah.mobile_error_url') . '?error=' . urlencode($errorMessage . ' | ' . $detail));
    }

    /**
     * Handle MyFatoorah Error Callback (User Cancelled or Direct Error)
     */
    public function error(Request $request)
    {
        $paymentId = $request->paymentId;
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
        
        return redirect(config('myfatoorah.mobile_error_url') . '?error=' . urlencode($errorMessage));
    }
}
