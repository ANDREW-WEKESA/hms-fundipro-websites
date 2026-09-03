<?php
/**
 * Services Configuration - FundiPro
 * Kenya-specific API integrations
 */
return [
    'mpesa' => [
        'consumer_key'    => getenv('MPESA_CONSUMER_KEY'),
        'consumer_secret' => getenv('MPESA_CONSUMER_SECRET'),
        'shortcode'       => getenv('MPESA_SHORTCODE'),
        'passkey'         => getenv('MPESA_PASSKEY'),
        'callback_url'    => getenv('MPESA_CALLBACK_URL'),
        'env'             => getenv('APP_ENV') === 'production' ? 'production' : 'sandbox',
    ],
    'africastalking' => [
        'api_key'   => getenv('AT_API_KEY'),
        'username'  => getenv('AT_USERNAME') ?: 'sandbox',
        'sender_id' => getenv('AT_SENDER_ID') ?: 'FundiPro',
    ],
    'subscription' => [
        'plans' => [
            'free'     => ['price' => 0,    'jobs_per_month' => 3],
            'pro'      => ['price' => 500,  'jobs_per_month' => null],
            'business' => ['price' => 1200, 'jobs_per_month' => null],
        ],
        'grace_period_days' => 3,
        'currency'          => 'KES',
    ],
    'app' => [
        'timezone' => 'Africa/Nairobi',
        'currency' => 'KES',
        'country'  => 'KE',
        'vat_rate' => 0.16,
        'emergency_numbers' => ['999', '112'],
    ],
];
