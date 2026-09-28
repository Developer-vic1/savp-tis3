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

    'peter3' => [
        'url' => env('PETER3_API_URL'),
        'connect_timeout' => (int) env('PETER3_CONNECT_TIMEOUT', 3),
        'timeout' => (int) env('PETER3_TIMEOUT', 10),
        'version' => env('PETER3_API_VERSION', 'v1'),
        'paths' => [
            'health' => env('PETER3_HEALTH_PATH', '/health'),
            'analysis' => env('PETER3_ANALYSIS_PATH'),
            'knowledge' => env('PETER3_KNOWLEDGE_PATH'),
            'tutor' => env('PETER3_TUTOR_PATH'),
        ],
    ],

];
