<?php

return [
    'payments' => [
        'currency' => env('CHAMBAPP_PAYMENT_CURRENCY', 'MXN'),
        'client_service_fee_percent' => env('CHAMBAPP_CLIENT_SERVICE_FEE_PERCENT', '15'),
        'professional_commission_percent' => env('CHAMBAPP_PROFESSIONAL_COMMISSION_PERCENT', '15'),
        // Legacy V1 setting retained for historical payments, tips and commerce.
        'platform_fee_percent' => env('CHAMBAPP_PLATFORM_FEE_PERCENT', '15'),
        'checkout_timeout' => (int) env('CHAMBAPP_PAYMENT_TIMEOUT', 10),
        'preference_lifetime_hours' => (int) env('CHAMBAPP_PAYMENT_PREFERENCE_HOURS', 24),
        'provider' => 'mercadopago',
    ],
    'commerce' => [
        'featured_prices' => [1 => '49.00', 7 => '199.00', 30 => '599.00'],
        'store_items' => [
            'theme-sunset' => ['kind' => 'theme', 'name' => 'Tema Atardecer', 'price' => '79.00', 'value' => 'sunset'],
            'frame-fire' => ['kind' => 'frame', 'name' => 'Marco Fuego', 'price' => '49.00', 'value' => 'fire'],
            'animation-glow' => ['kind' => 'animation', 'name' => 'Brillo sutil', 'price' => '39.00', 'value' => 'glow'],
            'banner-pro' => ['kind' => 'banner', 'name' => 'Banner Profesional', 'price' => '59.00', 'value' => 'pro'],
        ],
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
    'identity_verification' => [
        // Keep false until a KYC provider is selected, integrated and tested.
        'required' => filter_var(env('PROFESSIONAL_IDENTITY_VERIFICATION_REQUIRED', false), FILTER_VALIDATE_BOOL),
        'provider' => env('PROFESSIONAL_IDENTITY_VERIFICATION_PROVIDER'),
        'consent_version' => env('PROFESSIONAL_IDENTITY_CONSENT_VERSION', 'draft-2026-08-25'),
        'privacy_notice_version' => env('PRIVACY_NOTICE_VERSION', 'draft-2026-08-25'),
    ],
];
