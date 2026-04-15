<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MyFatoorah API Token (Live or Test)
    |--------------------------------------------------------------------------
    */
    'api_token' => env('MYFATOORAH_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode (Test or Live)
    |--------------------------------------------------------------------------
    | Set to true for testing, false for production
    */
    'is_sandbox' => env('MYFATOORAH_IS_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Callback / Redirect URLs
    |--------------------------------------------------------------------------
    | These URLs will be sent to MyFatoorah. Once payment is done, MyFatoorah
    | will POST/GET to these routes. Ensure your system parses them.
    */
    'callback_url' => env('MYFATOORAH_CALLBACK_URL', '/api/payment/myfatoorah/callback'),
    'error_url'    => env('MYFATOORAH_ERROR_URL', '/api/payment/myfatoorah/error'),
    'mobile_success_url' => env('MYFATOORAH_MOBILE_SUCCESS_URL', 'matraqa://payment-success'),
    'mobile_error_url'   => env('MYFATOORAH_MOBILE_ERROR_URL', 'matraqa://payment-failed'),
];
