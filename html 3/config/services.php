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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'vkontakte' => [
        'client_id' => env('VKONTAKTE_CLIENT_ID'),
        'client_secret' => env('VKONTAKTE_CLIENT_SECRET'),
        'redirect' => env('VKONTAKTE_REDIRECT_URI'),
        'lang' => 'ru'
    ],

    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
    ],

    'bova' => [
        'user_uuid' => env('BOVA_USER_UUID'),
        'api_key' => env('BOVA_API_KEY'),
        'base_url' => env('BOVA_BASE_URL', 'https://sandbox.bovatech.cc/'),
    ],

    'forest' => [
        'key_1' => env('FOREST_KEY_1'),
        'key_2' => env('FOREST_KEY_2'),
        'merchand_id' => env('FOREST_MERCHANT_ID'),
        'base_url' => env('FOREST_BASE_URL', 'https://pay.forestkassa.com/'),
    ],

    '7xpay' => [
        'public_key' => env('7XPAY_PUBLIC_KEY'),
        'secret_key' => env('7XPAY_SECRET_KEY'),
        'base_url' => env('7XPAY_BASE_URL', 'https://api.7xpay.pro/'),
    ],

    'cryptobot' => [
        'app_token' => env('CRYPTOBOT_APP_TOKEN'),
        'base_url' => env('CRYPTOBOT_BASE_URL', 'https://pay.crypt.bot/'),
    ],

];
