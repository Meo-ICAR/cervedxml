<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Third Party Services
     * |--------------------------------------------------------------------------
     * |
     * | This file is for storing the credentials for third party services such
     * | as Mailgun, Postmark, AWS and more. This file provides the de facto
     * | location for this type of information, allowing packages to have
     * | a conventional file to locate the various service credentials.
     * |
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
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'proxy' => env('PROXY'),  // Optional, will be used for all requests
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        //  'proxy' => env('PROXY')  // Optional, will be used for all requests
    ],
    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI'),
        //  'proxy' => env('PROXY')  // Optional, will be used for all requests
    ],
    'instagram_basic' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect' => env('INSTAGRAM_REDIRECT_URI'),
        //  'proxy' => env('PROXY')  // Optional, will be used for all requests
    ],
];
