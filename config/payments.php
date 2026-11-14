<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GCash QR payments
    |--------------------------------------------------------------------------
    |
    | Parishioners scan the parish GCash QR code, pay in the GCash app, then
    | enter the 13-digit reference number from their receipt. The parish office
    | checks that reference in its GCash account and confirms the payment.
    |
    | qr_image: path of the QR code picture inside the "public" folder.
    | account_name / account_number: shown under the QR code (optional).
    |
    */

    'currency' => 'PHP',

    'gcash' => [
        'qr_image' => env('GCASH_QR_IMAGE', 'images/gcash-qr.png'),
        'account_name' => env('GCASH_ACCOUNT_NAME'),
        'account_number' => env('GCASH_ACCOUNT_NUMBER'),
    ],

];
