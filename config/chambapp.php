<?php

return [
    'payments' => [
        'currency' => env('CHAMBAPP_PAYMENT_CURRENCY', 'MXN'),
        'platform_fee_percent' => env('CHAMBAPP_PLATFORM_FEE_PERCENT', '15'),
        'checkout_timeout' => (int) env('CHAMBAPP_PAYMENT_TIMEOUT', 10),
        'provider' => 'mercadopago',
    ],
    'on_demand' => [
        'immediate_request_timeout_minutes' => (int) env('CHAMBAPP_IMMEDIATE_TIMEOUT', 5),
        'invitation_timeout_minutes' => (int) env('CHAMBAPP_INVITATION_TIMEOUT', 3),
        'location_freshness_minutes' => (int) env('CHAMBAPP_LOCATION_FRESHNESS', 30),
        'search_radii_km' => [5, 10, 15, 25],
        'max_service_radius_km' => 25,
        'polling_interval_seconds' => 4,
        'service_radius_options_km' => [5, 10, 15, 25],
    ],
];
