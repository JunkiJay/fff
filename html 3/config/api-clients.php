<?php

return [
    'blvckpay' => [
        'base_url' => env('BLVCKPAY_API_BASE_URL', 'https://payment.blvckpay.com'),
        'signature' => env('BLVCKPAY_SIGNATURE', '887e6f5b-2e3e-4bd4-9b9e-90915b7cf734'),
        'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::RUB,
    ],
    'fk' => [
        'base_url' => env('FK_API_BASE_URL', 'https://api.fkwallet.io/v1/'),
        'public_key' => '6e5d287e58f5b655d56a71aa86a57306',
        'private_key' => 'U1WJXOaBd2UZhIRXH9wjZXQWRYHTaTqPTmvnT3UyjdQRZzSdeg',
        'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::RUB,
        'terminal_id' => 27585,
        'terminal_secret_1' => 'ng{U[)Lxh$dg(J*',
    ],
    'cryptobot' => [
        'base_url' => env('CRYPTOBOT_API_BASE_URL', 'https://pay.crypt.bot/api/'),
        'app_token' => env('CRYPTOBOT_API_TOKEN', '341318:AAuTlhgBGV6HZWVa0fpOoykCbmaEc6F3AcC'),
        'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::USDT,
    ],
    'paradise' => [
        'base_url' => env('PARADISE_API_BASE_URL', 'https://api.p2p-paradise.info/'),
        'shop_id' => 8,
        'api_secret' => 'prod_FdxJk1vWwn6whLOc4sQ3GF0g'
    ],
    'expay' => [
        'base_url' => env('EXPAY_API_BASE_URL', 'https://apiv2.expay.cash/api/'),
        'public_key' => 'mqbkjmrfgbx9dz05kdhx7g1v28n5doqbee7lpdfaco1v537kfbmwjyo7n91hxidl',
        'private_key' => 's9kax24d11iao5md5hmt5kx73m32lsfzol88pyz1uh9q7zi99cq0nv0fuujsgrz79am5cbte5h23xcx7b1jinmn9mixyr6rvflm3bl4ik2i9cdhvvjlyqg5rpr99fg8c',
    ],
    '1plat' => [
        'base_url' => env('ONEPLAT_API_BASE_URL', 'https://1plat.cash/api/'),
        'secret' => 'UB3W8GV378RH23UFIWEH52WEDFF',
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
];