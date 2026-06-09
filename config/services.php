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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'payfast' => [
        'merchant_id' => env('PAYFAST_MERCHANT_ID', ''),
        'merchant_key' => env('PAYFAST_MERCHANT_KEY', ''),
        'passphrase' => env('PAYFAST_PASSPHRASE', ''),
        'sandbox' => env('PAYFAST_SANDBOX', true),
        'checkout_url' => env('PAYFAST_CHECKOUT_URL', 'https://sandbox.payfast.co.za/eng/process'),
        'validation_url' => env('PAYFAST_VALIDATION_URL', 'https://sandbox.payfast.co.za/eng/query/validate'),
        'validate_itn_ip' => env('PAYFAST_VALIDATE_ITN_IP', true),
        'validate_itn_server' => env('PAYFAST_VALIDATE_ITN_SERVER', true),
        'valid_hosts' => [
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za',
        ],
    ],

];
