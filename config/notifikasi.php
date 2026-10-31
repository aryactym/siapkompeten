<?php

use App\Services\Notifications\HttpSmsGateway;
use App\Services\Notifications\LogSmsGateway;

return [
    'default_sms' => env('SMS_DRIVER', 'log'),

    'drivers' => [
        'log' => [
            'class' => LogSmsGateway::class,
        ],

        'http' => [
            'class' => HttpSmsGateway::class,
            'url' => env('SMS_API_URL'),
            'token' => env('SMS_API_TOKEN'),
            'sender' => env('SMS_SENDER_ID'),
        ],
    ],

    'retry' => [
        'tries' => 3,
        'backoff' => [60, 300, 900],
    ],
];
