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

    'gtx' => [
        'token' => env('GTX_TOKEN'),
        'secret_key' => env('GTX_SECRET_KEY'),
        'merchant_code' => env('GTX_MERCHANT_CODE'),
        'base_url' => env('GTX_BASE_URL', 'https://gtxpay.pro/api/v2'),
    ],

    'nirvana' => [
        'secret' => env('NIRVANA_SECRET'),
        'public' => env('NIRVANA_PUBLIC'),
        'base_url' => env('NIRVANA_BASE_URL', 'https://f.nirvanapay.pro/api'),
    ],

    '1plat' => [
        'shop_id' => env('1PLAT_SHOP_ID'),
        'secret' => env('1PLAT_SECRET'),
        'base_url' => env('1PLAT_BASE_URL', 'https://1plat.cash/api'),
    ],

    'p2plab' => [
        'api_key' => env('P2PLAB_API_KEY'),
        'secret' => env('P2PLAB_SECRET'),
        'base_url' => env('P2PLAB_BASE_URL', 'https://api.p2p-lab.com/api'),
    ],

    'repay' => [
        'cassa_api' => env('REPAY_CASSA_API'),
        'secret_1' => env('REPAY_SECRET_1'),
        'secret_2' => env('REPAY_SECRET_2'),
        'cassa_id' => env('REPAY_CASSA_ID'),
        'method_id_sbp_rub' => env('REPAY_METHOD_ID_SBP_RUB'),
        'method_id_oneclick' => env('REPAY_METHOD_ID_ONECLICK'),
        'method_id_tpay' => env('REPAY_METHOD_ID_TPAY'),
        'method_id_sberpay_rub' => env('REPAY_METHOD_ID_SBERPAY_RUB'),
        'base_url' => env('REPAY_BASE_URL', 'https://repay.cx/api/v1'),
    ],

];

        'secret_key' => env('GTX_SECRET_KEY'),
        'merchant_code' => env('GTX_MERCHANT_CODE'),
        'base_url' => env('GTX_BASE_URL', 'https://gtxpay.pro/api/v2'),
    ],

    'nirvana' => [
        'secret' => env('NIRVANA_SECRET'),
        'public' => env('NIRVANA_PUBLIC'),
        'base_url' => env('NIRVANA_BASE_URL', 'https://f.nirvanapay.pro/api'),
    ],

    '1plat' => [
        'shop_id' => env('1PLAT_SHOP_ID'),
        'secret' => env('1PLAT_SECRET'),
        'base_url' => env('1PLAT_BASE_URL', 'https://1plat.cash/api'),
    ],

    'p2plab' => [
        'api_key' => env('P2PLAB_API_KEY'),
        'secret' => env('P2PLAB_SECRET'),
        'base_url' => env('P2PLAB_BASE_URL', 'https://api.p2p-lab.com/api'),
    ],

    'repay' => [
        'cassa_api' => env('REPAY_CASSA_API'),
        'secret_1' => env('REPAY_SECRET_1'),
        'secret_2' => env('REPAY_SECRET_2'),
        'cassa_id' => env('REPAY_CASSA_ID'),
        'method_id_sbp_rub' => env('REPAY_METHOD_ID_SBP_RUB'),
        'method_id_oneclick' => env('REPAY_METHOD_ID_ONECLICK'),
        'method_id_tpay' => env('REPAY_METHOD_ID_TPAY'),
        'method_id_sberpay_rub' => env('REPAY_METHOD_ID_SBERPAY_RUB'),
        'base_url' => env('REPAY_BASE_URL', 'https://repay.cx/api/v1'),
    ],

];
