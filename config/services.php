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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ], 

    'zoom' => [
        'account_id' => env('ZOOM_ACCOUNT_ID'),
        'client_id' => env('ZOOM_CLIENT_ID'), // Used for Server-to-Server OAuth (API calls)
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
        // Separate credentials for Meeting SDK (client view)
        'sdk_client_id' => env('ZOOM_SDK_CLIENT_ID', env('ZOOM_CLIENT_ID')), 
        'sdk_client_secret' => env('ZOOM_SDK_CLIENT_SECRET', env('ZOOM_CLIENT_SECRET')),
    ],

    'whatsapp' => [
        'token' => env('META_WHATSAPP_TOKEN'),
        'phone_number_id' => env('META_WHATSAPP_PHONE_ID'),
        'business_id' => env('META_WHATSAPP_BUSINESS_ID'),
    ],

    'paytm-wallet' => [
            'env' => env('PAYTM_ENVIRONMENT'), // values : (local | production)
            'merchant_id' => env('PAYTM_MERCHANT_ID'),
            'merchant_key' => env('PAYTM_MERCHANT_KEY'),
            'merchant_website' => env('PAYTM_MERCHANT_WEBSITE'),
            'channel' => env('PAYTM_CHANNEL'),
            'industry_type' => env('PAYTM_INDUSTRY_TYPE'),
    ], 

    'gstin' => [
        'url' => env('GSTIN_API_URL'),
        'key' => env('GSTIN_API_KEY'),
    ],

];
