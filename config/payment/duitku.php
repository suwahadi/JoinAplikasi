<?php
/**
 * Konfigurasi Duitku Payment Gateway
 */

return [
    'merchant_code' => env('DUITKU_MERCHANT_CODE', ''),
    'api_key' => env('DUITKU_API_KEY', ''),
    'environment' => env('DUITKU_ENV', 'sandbox'),
    'base_url' => [
        'sandbox' => 'https://sandbox.duitku.com/webapi/api/merchant',
        'production' => 'https://passport.duitku.com/webapi/api/merchant',
    ],
    'callback_url' => env('DUITKU_CALLBACK_URL', '/api/payment/duitku/callback'),
    'return_url' => env('DUITKU_RETURN_URL', '/payment/return'),
    'timeout' => 30,
    'log_requests' => env('DUITKU_LOG_REQUESTS', true),
    'log_channel' => 'daily',
    'verify_callback_signature' => true,
];
