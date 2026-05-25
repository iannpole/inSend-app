<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk payment gateway Midtrans.
    | Daftar di https://dashboard.midtrans.com (sandbox)
    | atau https://account.midtrans.com (production)
    |
    */

    'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'merchant_id'   => env('MIDTRANS_MERCHANT_ID', ''),

    // Snap API URL
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    // API URL for status check
    'api_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2',

    // Enabled payment methods
    'enabled_payments' => [
        'gopay',
        'shopeepay',
        'qris',
        'bank_transfer',
        'bca_va',
        'bni_va',
        'bri_va',
        'permata_va',
        'echannel', // Mandiri Bill
        'other_qris',
    ],

    // Expiry duration in minutes
    'expiry_duration' => env('MIDTRANS_EXPIRY_MINUTES', 1440), // 24 hours
];
