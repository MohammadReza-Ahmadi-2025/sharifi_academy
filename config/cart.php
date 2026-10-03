<?php

return [
    'session_key' => 'cart',

    // آیتم‌های سبد کاربران لاگین‌شده پس از این مدت بی‌حرکت پاک می‌شوند.
    'expired_after_minutes' => (int) env('CART_EXPIRED_AFTER_MINUTES', 30),
];
