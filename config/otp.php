<?php

return [

    'code_length' => (int) env('OTP_CODE_LENGTH', 6),

    'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 5),

    'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN', 120),

    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 3),

    'default_type' => 'login',

    'rate_limit' => [
        'mobile' => [
            'max_attempts' => (int) env('OTP_RATE_MOBILE_MAX', 5),
            'decay_minutes' => (int) env('OTP_RATE_MOBILE_DECAY', 60),
        ],
        'ip' => [
            // بیش از این تعداد درخواست SMS از یک IP → مسدود تا پایان decay
            'max_attempts' => (int) env('OTP_RATE_IP_MAX', 10),
            'decay_minutes' => (int) env('OTP_RATE_IP_DECAY', 120),
        ],
    ],

    /** OTP signup does not assign a role; student role is granted on confirmed enrollment. */
    'new_user_role' => null,

];
