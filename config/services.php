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

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'api_key' => env('FIREBASE_API_KEY'),
        'service_account_path' => env('FIREBASE_SERVICE_ACCOUNT_PATH', storage_path('app/secrets/firebase-service-account.json')),
    ],

    'sms' => [
        // REVE SMS (smpp.revesms.com) gateway credentials.
        'api_key' => env('SMS_API_KEY'),
        'secret_key' => env('SMS_SECRET_KEY'),
        'sender_id' => env('SMS_SENDER_ID'),
        'base_url' => env('SMS_BASE_URL', 'https://smpp.revesms.com:7790'),

        // Demo login for app-store reviewers (Play Console, App Store review,
        // etc). This phone number always resolves to a fixed, non-expiring
        // OTP and never triggers a real SMS - reviewers can't receive texts
        // during automated review. Never expose these values client-side.
        'demo_phone' => env('SMS_DEMO_PHONE'),
        'demo_otp' => env('SMS_DEMO_OTP'),
    ],

];
