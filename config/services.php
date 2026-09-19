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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'return_url' => env('RETURN_URL_PAGAMENTO'),
    ],

    'payment' => [
        'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),
    ],
    'fipe' => [
        'base_url' => env('FIPE_API_BASE_URL', 'https://fipe.parallelum.com.br/api/v2'),
        'legacy_base_url' => env('FIPE_API_LEGACY_BASE_URL', 'https://parallelum.com.br/fipe/api/v1'),
        'vehicle_type' => env('FIPE_VEHICLE_TYPE', 'cars'),
        'vehicle_types' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('FIPE_VEHICLE_TYPES', 'cars,motorcycles,trucks'))
        ))),
        'reference' => env('FIPE_API_REFERENCE'),
        'token' => env('FIPE_API_TOKEN'),
    ],
    'nuvemfiscal' => [
        'auth_url' => env('NUVEMFISCAL_AUTH_URL', 'https://auth.nuvemfiscal.com.br/oauth/token'),
        'base_url' => env('NUVEMFISCAL_BASE_URL', 'https://api.nuvemfiscal.com.br'),
        'sandbox_url' => env('NUVEMFISCAL_SANDBOX_URL', 'https://api.sandbox.nuvemfiscal.com.br'),
    ],
    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY', ''),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],
    'openai' => [
        'key' => env('OPENAI_API_KEY', ''),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],
];
