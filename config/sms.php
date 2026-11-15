<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "log", "winsms", "twilio", "tunisiesms"
    | En développement / démo : "log" (enregistre les SMS dans storage/logs/laravel.log)
    | En production : "winsms" ou "tunisiesms" ou "twilio"
    |
    */

    'default' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SMS Providers Configuration
    |--------------------------------------------------------------------------
    */

    'providers' => [

        'log' => [
            'driver' => 'log',
        ],

        'winsms' => [
            'driver' => 'winsms',
            'api_key' => env('WINSMS_KEY'),
            'sender' => env('WINSMS_SENDER', 'FromDarna'),
            'endpoint' => env('WINSMS_ENDPOINT', 'https://www.winsms.tn/api/sms/send'),
        ],

        'tunisiesms' => [
            'driver' => 'tunisiesms',
            'app_id' => env('TUNISIESMS_APP_ID'),
            'app_secret' => env('TUNISIESMS_APP_SECRET'),
            'sender' => env('TUNISIESMS_SENDER', 'FromDarna'),
            'endpoint' => env('TUNISIESMS_ENDPOINT', 'https://www.tunisiesms.tn/api/send_sms'),
        ],

        'twilio' => [
            'driver' => 'twilio',
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_TOKEN'),
            'from' => env('TWILIO_FROM', 'FromDarna'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | OTP & Anti-Abus Rules
    |--------------------------------------------------------------------------
    */

    'otp' => [
        'digits' => 6,
        'ttl_minutes' => 10,
        'max_attempts' => 5,
        'max_per_hour' => 5, // Limitation SMS Pumping
    ],

];
