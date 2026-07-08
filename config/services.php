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

    'ors' => [
        'base_url' => env('ORS_BASE_URL', 'http://localhost:8080'),
    ],

    'libretranslate' => [
        'url' => env('LIBRETRANSLATE_URL', 'http://localhost:5000'),
        'key' => env('LIBRETRANSLATE_API_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    'tour_guide' => [
        // WhatsApp number for manual tour guide coordination (international format without "+", e.g. 6281234567890)
        'whatsapp' => env('TOUR_GUIDE_WHATSAPP_NUMBER'),
    ],

    'penglipuran' => [
        'latitude' => env('PENGLIPURAN_LAT', -8.422303596762355),
        'longitude' => env('PENGLIPURAN_LON', 115.35948833933173),
        'zoom' => (int) env('PENGLIPURAN_ZOOM', 17),
        'timezone' => env('PENGLIPURAN_TIMEZONE', 'Asia/Makassar'),
        'geofence_radius' => (int) env('PENGLIPURAN_GEOFENCE_RADIUS', 500),
    ],

];
