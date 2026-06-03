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
    ],

    'payment' => [
        'status_pending' => 'pending',
        'status_completed' => 'completed',
        'status_failed' => 'failed',
        'status_refunded' => 'refunded',
    ],
];
