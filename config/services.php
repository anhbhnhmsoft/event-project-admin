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

    'resend' => [
        'key' => env('RESEND_KEY'),
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
        'map_key_api' => env('GOOGLE_MAPS_API_KEY'),
        'map_id' => env('GOOGLE_MAP_ID'),
    ],
    'node_server' => [
        'access_token' => env('APP_KEY_NODE_SERVER'),
        'notification_url' => env('APP_URL_NODE') . 'send-notification',
    ],
    'node_server' => [
        'access_token' => env('APP_KEY_NODE_SERVER'),
        'notification_url' => env('APP_URL_NODE') . 'send-notification',
    ],

    'otp' => [
        'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 10),
        'rate_limit' => [
            'count' => env('OTP_RATE_LIMIT_COUNT', 3),
            'minutes' => env('OTP_RATE_LIMIT_MINUTES', 10),
        ],
    ],

    'revenuecat' => [
        'api_key' => env('REVENUECAT_API_KEY'),
        'webhook_secret' => env('REVENUECAT_WEBHOOK_SECRET'),
        'public_sdk_key' => env('REVENUECAT_PUBLIC_SDK_KEY'),
        'project_id' => env('REVENUECAT_PROJECT_ID'),
    ],
];
