<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Online payment gateway
    |--------------------------------------------------------------------------
    |
    | "simulator" runs a local GCash checkout page so the full flow can be
    | demonstrated without real money. "paymongo" sends parishioners to a real
    | PayMongo GCash checkout (use test keys first: sk_test_...).
    |
    */

    'gateway' => env('PAYMENT_GATEWAY', 'simulator'),

    // GCash checkouts below this amount are rejected by PayMongo.
    'minimum_online_amount' => (float) env('PAYMENT_MINIMUM_ONLINE_AMOUNT', 20),

    'currency' => 'PHP',

    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
    ],

];
