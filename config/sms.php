<?php

return [

    'drivers' => [
        'smsir' => [
            'base_url' => env('SMSIR_BASE_URL', 'https://api.sms.ir/v1'),
            'api_key' => env('SMS_API_KEY'),
        ],
    ],

    'templates' => [
        'login' => 739984,
    ],

];
