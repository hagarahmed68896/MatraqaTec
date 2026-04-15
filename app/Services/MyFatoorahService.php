<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyFatoorahService
{
    protected $apiToken;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiToken = config('myfatoorah.api_token');
        $isSandbox = config('myfatoorah.is_sandbox');
        
        // Use Saudi specific live URL if token implies it, otherwise standard live
        $this->baseUrl = $isSandbox 
            ? 'https://apitest.myfatoorah.com' 
            : 'https://api-sa.myfatoorah.com';
    }

    /**
     * Send Payment to MyFatoorah
     *
     * @param float $amount
     * @param string $customerName
     * @param string $customerEmail
     * @param string $customerMobile
     * @param string $referenceId
     * @return array|null Returns [InvoiceURL, InvoiceId] or null on failure
     */
    public function sendPayment($amount, $customerName, $customerEmail, $customerMobile, $referenceId)
    {
        // MyFatoorah strict 11-character limit fix:
        $cleanMobile = preg_replace('/[^0-9]/', '', $customerMobile);
        if (strlen($cleanMobile) > 11) {
            // Keep the last 10 digits (e.g. 05xxxxxxxx) to drop country codes
            $cleanMobile = substr($cleanMobile, -10);
        }

        $payload = [
            'NotificationOption' => 'Lnk',
            'InvoiceValue'       => $amount,
            'CustomerName'       => $customerName ?: 'Customer',
            'DisplayCurrencyIso' => 'SAR',
            'CustomerEmail'      => $customerEmail ?: 'test@test.com',
            'CustomerMobile'     => $cleanMobile,
            'CallBackUrl'        => config('myfatoorah.callback_url'),
            'ErrorUrl'           => config('myfatoorah.error_url'),
            'Language'           => 'ar',
            'CustomerReference'  => $referenceId,
        ];

        try {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/v2/SendPayment", $payload);

            if ($response->successful() && $response->json('IsSuccess')) {
                return [
                    'InvoiceURL' => $response->json('Data.InvoiceURL'),
                    'InvoiceId'  => $response->json('Data.InvoiceId'),
                ];
            }

            Log::error('MyFatoorah SendPayment Error: ', $response->json() ?? []);
            return ['error' => $response->json()];
        } catch (\Exception $e) {
            Log::error('MyFatoorah SendPayment Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Payment Status from MyFatoorah
     *
     * @param string $paymentId
     * @return array|null
     */
    public function getPaymentStatus($paymentId)
    {
        $payload = [
            'Key'     => $paymentId,
            'KeyType' => 'PaymentId',
        ];

        try {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/v2/GetPaymentStatus", $payload);

            if ($response->successful() && $response->json('IsSuccess')) {
                return $response->json('Data');
            }

            Log::error('MyFatoorah GetPaymentStatus Error: ', $response->json() ?? []);
            return null;
        } catch (\Exception $e) {
            Log::error('MyFatoorah GetPaymentStatus Exception: ' . $e->getMessage());
            return null;
        }
    }
}
