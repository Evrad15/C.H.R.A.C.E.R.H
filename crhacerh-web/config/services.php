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
        'with' => [
            'access_type' => 'offline',   // Demande un accès permanent
            'prompt' => 'consent',        // Force l'apparition de l'écran d'autorisation
        ],
    ],

    'africastalking' => [
        'username'  => env('AFRICASTALKING_USERNAME', 'sandbox'),
        'api_key'   => env('AFRICASTALKING_API_KEY'),
        'sender_id' => env('AFRICASTALKING_SENDER_ID', 'CHRACERH'),
    ],

    'camoo' => [
        'api_key'    => env('CAMOO_API_KEY'),
        'api_secret' => env('CAMOO_API_SECRET'),
        'sender_id'  => env('CAMOO_SENDER_ID', 'CHRACERH'),
    ],

    'orange' => [
        'client_id'      => env('ORANGE_CLIENT_ID'),
        'client_secret'  => env('ORANGE_CLIENT_SECRET'),
        'auth_header'    => env('ORANGE_AUTH_HEADER'),
        'sender_address' => env('ORANGE_SENDER_ADDRESS', 'tel:+2370000'),
        'sender_name'    => env('ORANGE_SENDER_NAME', 'CHRACERH'),
    ],

];
