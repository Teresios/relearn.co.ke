<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Safaricom M-Pesa Daraja API integration.
    | Set these values in your .env file.
    |
    */

    // Environment: 'sandbox' or 'production'
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    // Consumer Key and Secret from Safaricom Daraja Portal
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),

    // Business shortcode (Paybill or Till Number)
    'shortcode' => env('MPESA_SHORTCODE', ''),

    // Lipa Na M-Pesa Online passkey
    'passkey' => env('MPESA_PASSKEY', ''),

    // Callback URLs
    'callback_url' => env('MPESA_CALLBACK_URL', ''),
    'timeout_url' => env('MPESA_TIMEOUT_URL', ''),

    // B2C Configuration
    'b2c_shortcode' => env('MPESA_B2C_SHORTCODE', ''),
    'b2c_initiator' => env('MPESA_B2C_INITIATOR', ''),
    'b2c_password' => env('MPESA_B2C_PASSWORD', ''),
    'b2c_result_url' => env('MPESA_B2C_RESULT_URL', ''),
    'b2c_queue_timeout_url' => env('MPESA_B2C_QUEUE_TIMEOUT_URL', ''),
];
