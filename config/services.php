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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sendgrid' => [
        'key' => env('SENDGRID_API_KEY'),
        'events_list_id' => env('SENDGRID_EVENTS_LIST_ID'),
        'event_title_field' => env('SENDGRID_EVENT_TITLE_FIELD'),
        'event_date_field' => env('SENDGRID_EVENT_DATE_FIELD'),
        'from_email' => env('SENDGRID_FROM_EMAIL', env('SENDGRID_FROM_ADDRESS', env('MAIL_FROM_ADDRESS'))),
        'from_name' => env('SENDGRID_FROM_NAME', env('MAIL_FROM_NAME', config('app.name'))),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'terminal_location' => env('STRIPE_TERMINAL_LOCATION_ID'),
    ],

    'tap_to_pay' => [
        'environment' => env('TAP_TO_PAY_ENV', 'UAT'),
        'tpn' => env('TAP_TO_PAY_TPN'),
        'merchant_code' => env('TAP_TO_PAY_MERCHANT_CODE'),
        'auth_token' => env('TAP_TO_PAY_AUTH_TOKEN'),
    ],

];
