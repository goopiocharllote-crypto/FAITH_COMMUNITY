<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    /*
    | SMS notifications. "log" writes messages to storage/logs/laravel.log;
    | "semaphore" sends real SMS through https://semaphore.co and "iprog"
    | through https://www.iprogsms.com (both Philippines).
    */
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
    ],

    'semaphore' => [
        'api_key' => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME'),
        'url' => env('SEMAPHORE_URL', 'https://api.semaphore.co/api/v4/messages'),
    ],

    'iprog' => [
        'api_token' => env('IPROG_API_TOKEN'),
        'url' => env('IPROG_URL', 'https://www.iprogsms.com/api/v1/sms_messages'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
