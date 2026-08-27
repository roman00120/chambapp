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
        // OAuth authorization links must have a short, server-enforced lifetime.
        'oauth_state_lifetime_seconds' => (int) env('MERCADOPAGO_OAUTH_STATE_LIFETIME', 600),
        'read_retry_attempts' => (int) env('MERCADOPAGO_READ_RETRY_ATTEMPTS', 3),
        'read_retry_base_milliseconds' => (int) env('MERCADOPAGO_READ_RETRY_BASE_MILLISECONDS', 250),
        'read_retry_max_milliseconds' => (int) env('MERCADOPAGO_READ_RETRY_MAX_MILLISECONDS', 2000),
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
    'creator_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('CHAMBAPP_CREATOR_EMAILS', 'gerawx@gmail.com,romy00120@gmail.com'))))),
    'identity_verification' => [
        // Keep false until a KYC provider is selected, integrated and tested.
        'required' => filter_var(env('PROFESSIONAL_IDENTITY_VERIFICATION_REQUIRED', false), FILTER_VALIDATE_BOOL),
        'provider' => env('PROFESSIONAL_IDENTITY_VERIFICATION_PROVIDER'),
        'consent_version' => env('PROFESSIONAL_IDENTITY_CONSENT_VERSION', 'draft-2026-08-25'),
        'privacy_notice_version' => env('PRIVACY_NOTICE_VERSION', 'draft-2026-08-25'),
        'transfer_ttl_minutes' => (int) env('PROFESSIONAL_IDENTITY_TRANSFER_TTL_MINUTES', 10),
        'polling_interval_seconds' => 5,
    ],
    'legal' => [
        // Keep disabled until counsel approves complete, non-draft documents.
        'registration_acceptance_required' => filter_var(env('LEGAL_REGISTRATION_ACCEPTANCE_REQUIRED', false), FILTER_VALIDATE_BOOL),
        'documents_final' => filter_var(env('LEGAL_DOCUMENTS_FINAL', false), FILTER_VALIDATE_BOOL),
        'professional_terms_enabled' => filter_var(env('PROFESSIONAL_TERMS_ENABLED', false), FILTER_VALIDATE_BOOL),
        'documents' => [
            'terms' => [
                'title' => 'Términos y Condiciones',
                'version' => env('TERMS_VERSION', 'draft-2026-08-25'),
                'route' => 'legal.terms',
                'enabled' => true,
            ],
            'privacy' => [
                'title' => 'Aviso de Privacidad',
                'version' => env('PRIVACY_NOTICE_VERSION', 'draft-2026-08-25'),
                'route' => 'legal.privacy',
                'enabled' => true,
            ],
        ],
    ],
];
