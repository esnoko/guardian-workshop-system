<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Workshop Registration Configuration
    |--------------------------------------------------------------------------
    */

    'registration' => [
        'vat_rate' => 0.15, // 15% VAT
        'max_tickets_per_registration' => 3,
        'reference_number_prefix' => 'REF',
        'pending_expiry_minutes' => 120,
    ],

    'payment' => [
        'status_pending' => 'pending',
        'status_completed' => 'completed',
        'status_failed' => 'failed',
        'status_refunded' => 'refunded',
    ],
];
