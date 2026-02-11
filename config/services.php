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

    'clover' => [
        'app_id' => env('CLOVER_APP_ID'),
        'app_access_token' => env('CLOVER_APP_ACCESS_TOKEN'),
        'metered_event_id' => env('CLOVER_METERED_EVENT_ID'),
        'loyalty_metered_event_id' => env('CLOVER_LOYALTY_METERED_EVENT_ID'),
        'order_type_id' => env('CLOVER_ORDER_TYPE_ID'),
        'pickup_order_type_id' => env('CLOVER_PICKUP_ORDER_TYPE_ID'),
        'live_metrics' => filter_var(env('CLOVER_LIVE_METRICS', false), FILTER_VALIDATE_BOOLEAN),
    ],

    'twilio' => [
        'sid' => env('TWILIO_ACCOUNT_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'service_account' => env('FCM_SERVICE_ACCOUNT', storage_path('app/fcm-service-account.json')),
    ],

];
