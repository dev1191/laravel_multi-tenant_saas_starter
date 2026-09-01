<?php

return [
    'default_gateway' => env('DEFAULT_BILLING_GATEWAY', 'stripe'),
    'supported_currencies' => ['USD', 'EUR', 'GBP', 'BRL', 'INR'],
    'trial_days' => 14,
];
