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
    'b2c_shortcode' => env('MPESA_B2C_SHORTCODE', env('MPESA_SHORTCODE', '')),
    'initiator_name' => env('MPESA_INITIATOR_NAME', ''),
    'security_credential' => env('MPESA_SECURITY_CREDENTIAL', ''),
    'b2c_result_url' => env('MPESA_B2C_RESULT_URL', env('MPESA_CALLBACK_URL', '')),
    'b2c_timeout_url' => env('MPESA_B2C_TIMEOUT_URL', env('MPESA_TIMEOUT_URL', '')),

    // B2B Configuration
    'b2b_shortcode' => env('MPESA_B2B_SHORTCODE', env('MPESA_SHORTCODE', '')),
    'b2b_result_url' => env('MPESA_B2B_RESULT_URL', env('MPESA_CALLBACK_URL', '')),
    'b2b_timeout_url' => env('MPESA_B2B_TIMEOUT_URL', env('MPESA_TIMEOUT_URL', '')),
];
