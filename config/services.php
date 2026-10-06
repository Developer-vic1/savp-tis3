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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'aporte_ingenieril' => [
        // Solo se habilita tras aprobar el contrato y el entorno de integración.
        'enabled' => env('APORTE_INGENIERIL_ENABLED', false),
        'url' => env('APORTE_INGENIERIL_BASE_URL'),
        'connect_timeout' => (int) env('APORTE_INGENIERIL_CONNECT_TIMEOUT', 3),
        'timeout' => (int) env('APORTE_INGENIERIL_REQUEST_TIMEOUT', 10),
        'cold_start_timeout' => (int) env('APORTE_INGENIERIL_COLD_START_TIMEOUT', 90),
        'version' => env('APORTE_INGENIERIL_API_VERSION', 'v1'),
        'key' => env('APORTE_INGENIERIL_API_KEY'),
        'allowed_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('APORTE_INGENIERIL_ALLOWED_HOSTS', '127.0.0.1,localhost,::1'))
        ))),
        'paths' => [
            'health' => env('APORTE_INGENIERIL_HEALTH_PATH', '/health'),
            'analysis' => env('APORTE_INGENIERIL_ANALYSIS_PATH'),
            'knowledge' => env('APORTE_INGENIERIL_KNOWLEDGE_PATH'),
            'knowledge_governance' => env('APORTE_INGENIERIL_KNOWLEDGE_GOVERNANCE_PATH'),
            'tutor' => env('APORTE_INGENIERIL_TUTOR_PATH'),
        ],
    ],

];
