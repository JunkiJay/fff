<?php

return [
    'blvckpay' => [
        'base_url' => env('BLVCKPAY_API_BASE_URL', 'https://payment.blvckpay.com'),
        'signature' => env('BLVCKPAY_SIGNATURE', '887e6f5b-2e3e-4bd4-9b9e-90915b7cf734'),
        'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::RUB,
    ],
    'fk' => [
        'base_url' => env('FK_API_BASE_URL', 'https://api.fkwallet.io/v1/'),
        'public_key' => 'c8d12bfd83b54373912e57c84fa3f1c0',
        'private_key' => 'tSq7058UPn1uDRJB4kF71tHfAt3fWxyUyGWdj2p8n5KPmFRxPv',
        'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::RUB,
        'terminal_id' => 27585,
        'terminal_secret_1' => '$Z/gj52&Rhi7G%t',
        'terminal_secret_2' => 'T(0pAYR1P$]l@TN',
    ],
    'cryptobot' => [
        'base_url' => env('CRYPTOBOT_API_BASE_URL', 'https://pay.crypt.bot/api/'),
        'app_token' => env('CRYPTOBOT_API_TOKEN', '341318:AA9V8L5HGvC8UIIfHJjpwdSWamBdOWO1c5p'),
        'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::USDT,
    ],
    'paradise' => [
        // ВАЖНО: закрывающий слэш нужен, чтобы Guzzle ходил именно на /api/payments,
        // а не на /payments
        'base_url' => env('PARADISE_API_BASE_URL', 'https://p2paradise.net/api/'),
        'shop_id' => env('PARADISE_SHOP_ID', 8),
        'api_secret' => env('PARADISE_API_SECRET', 'prod_p5psWNO5OzxzdT4YInK7xpvy')
    ],
    'pspay' => [
        'base_url' => env('PSPAY_API_BASE_URL', 'https://p2paradise.net/'),
        'shop_id' => 8,
        'api_secret' => 'prod_p5psWNO5OzxzdT4YInK7xpvy'
    ],
    'expay' => [
        'base_url' => env('EXPAY_API_BASE_URL', 'https://apiv2.expay.cash/api/'),
        'public_key' => 'mqbkjmrfgbx9dz05kdhx7g1v28n5doqbee7lpdfaco1v537kfbmwjyo7n91hxidl',
        'private_key' => 's9kax24d11iao5md5hmt5kx73m32lsfzol88pyz1uh9q7zi99cq0nv0fuujsgrz79am5cbte5h23xcx7b1jinmn9mixyr6rvflm3bl4ik2i9cdhvvjlyqg5rpr99fg8c',
    ],
    '1plat' => [
        'base_url' => env('ONEPLAT_API_BASE_URL', 'https://1plat.cash/api/'),
        'shop_id' => 11,
        'secret' => 'OJKN23UIH23U8I23RF32G',
    ],
    'onepayments' => [
        'base_url' => env('ONEPAYMENTS_API_BASE_URL', 'http://onepayments.tech/api/'),
        'api_key' => '830230f47120c6de6718133ab8679358c71f593376be68ed',
    ],
    'gotham' => [
        'base_url' => env('GOTHAM_API_BASE_URL', 'https://gotham-trade.com/api/'),
        'user_name' => 'StimuleWin',
        'api_key' => 'S7zaVEASfDb6Ph7JWeUzGrsgF5mMttra',
    ],
    'gtx' => [
        'base_url' => env('GTX_API_BASE_URL', 'https://gtxpay.pro/api/v2/'),
        'api_token' => '47|lfbXLyHHP0EOI7ievK6f22isT67rFa0nHJR7mcvy',
        'api_secret_key' => '4beee862-23af-4cda-a397-bd2f33281cc3',
        'merchant_id' => 'diyq0qd3ww2kwfzwt22kxbrc',
    ],
    'spinpay' => [
        'base_url' => env('SPINPAY_API_BASE_URL', 'https://business.cixsdpxj.info/api/v1/'),
        'token' => 'b99b5e53ecac311b748f2fc8c4cfacfdc631',
        'private_key' => '5FLawIh6EjZYIwyQPmZO',
        'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::RUB,
    ],
];