<?php

declare(strict_types=1);

use App\Services\Payments\Enum\PaymentProviderIconsEnum;
use App\Services\Payments\Enum\PaymentProvidersEnum;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Providers\BlvcpayPymentProvider;
use App\Services\Payments\Providers\CascadePaymentProvider;
use App\Services\Payments\Providers\CryptobotPaymentProvider;
use App\Services\Payments\Providers\ExpayPaymentProvider;
use App\Services\Payments\Providers\FKPaymentProvider;
use App\Services\Currencies\Enums\CurrenciesEnum;
use App\Services\Payments\Providers\OnePayPaymentProvider;
use App\Services\Payments\Providers\OnePlatPaymentProvider;
use App\Services\Payments\Providers\ParadisePaymentProvider;
use App\Services\Payments\Providers\USDTPaymentProvider;

return [
    'providers' => [
        PaymentProvidersEnum::CRYPTOBOT->value => [
            'class' => CryptobotPaymentProvider::class,
            'base_currency' => CurrenciesEnum::USDT,
            'provider' => PaymentProvidersEnum::CRYPTOBOT,
            'payment' => [
                [
                    'method' => PaymentMethodEnum::CRYPTOBOT,
                    'min' => 1,
                    'max' => 300000,
                    'bonus_percent' => 10,
                    'hot' => true,
                    'position' => 1,
                    'image' => '/assets/withdraw/bank-cryptobot.png',
                ]
            ],
            'withdraw' => [
                [
                    'method' => PaymentMethodEnum::CRYPTOBOT,
                    'min' => 500,
                    'hot' => true,
                    'hidden' => false,
                    'position' => 1,
                    'commission_percents' => 0,
                    'image' => '/assets/withdraw/bank-cryptobot.png',
                ]
            ],
        ],
        PaymentProvidersEnum::FK->value => [
            'class' => FKPaymentProvider::class,
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::FK,
            'payment' => [
                [
                    'method' => PaymentMethodEnum::FK,
                    'min' => 10,
                    'max' => 300000,
                    'bonus_percent' => 5,
                    'hot' => true,
                    'position' => 2,
                    'image' => '/assets/withdraw/bank-fk.png',
                ]
            ],
            'withdraw' => [
                [
                    'method' => PaymentMethodEnum::FK,
                    'min' => 500,
                    'hot' => true,
                    'hidden' => false,
                    'position' => 2,
                    'commission_percents' => 5,
                    'image' => '/assets/withdraw/bank-fk.png',
                ]
            ]
        ],
        PaymentProvidersEnum::BLVCKPAY->value => [
            'class' => BlvcpayPymentProvider::class,
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::BLVCKPAY,
            'payment' => [
                [
                    'method' => PaymentMethodEnum::SBP_QR,
                    'min' => 500,
                    'max' => 300000,
                    'min_payments_count' => 1,
                    'hot' => true,
                    'position' => 3,
                    'image' => '/assets/withdraw/bank-sb.png',
                ]
            ],
        ],
        PaymentProvidersEnum::FROM_100_SBP_CASCADE->value => [
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::FROM_100_SBP_CASCADE,
            'class' => CascadePaymentProvider::class,
            'payment' => [
                [
                    'cascade' => [
                        PaymentProvidersEnum::PARADISE,
                        PaymentProvidersEnum::ONE_PLAT,
                    ],
                    'first_bonus_granted' => true,
                    'method' => PaymentMethodEnum::SBP,
                    'min' => 100,
                    'max' => 300000,
                    'image' => '/assets/withdraw/bank-sb.png',
                ]
            ]
        ],
        PaymentProvidersEnum::PARADISE->value => [
            'class' => ParadisePaymentProvider::class,
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::PARADISE,
            'payment' => [
                [
                    'hidden' => true,
                    'method' => PaymentMethodEnum::SBP,
                    'min' => 100,
                    'max' => 300000,
                    'image' => '/assets/withdraw/bank-sb.png',
                ]
            ],
        ],
        PaymentProvidersEnum::ONE_PLAT->value => [
            'class' => OnePlatPaymentProvider::class,
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::ONE_PLAT,
            'payment' => [
                [
                    'hidden' => true,
                    'min' => 100,
                    'max' => 300000,
                    'method' => PaymentMethodEnum::SBP,
                    'image' => '/assets/withdraw/bank-sb.png',
                ]
            ],
        ],
        PaymentProvidersEnum::GTX->value => [
            'class' => \App\Services\Payments\Providers\GTXPaymentProvider::class,
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::GTX,
            'payment' => [
                [
                    'hidden' => true,
                    'min' => 3000,
                    'max' => 300000,
                    'method' => PaymentMethodEnum::C2C,
                    'image' => '/assets/withdraw/bank-sb.png',
                ]
            ],
        ],
        PaymentProvidersEnum::EXPAY->value => [
            'class' => ExpayPaymentProvider::class,
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::EXPAY,
            'payment' => [
                [
                    'hidden' => false,
                    'min' => 2000,
                    'max' => 300000,
                    'method' => PaymentMethodEnum::C2C,
                    'image' => '/assets/withdraw/bank-cards.png',
                ]
            ],
        ],
        PaymentProvidersEnum::ONEPAY->value => [
            'class' => OnePayPaymentProvider::class,
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::ONEPAY,
            'withdraw' => [
                [
                    'min' => 3200,
                    'max' => 300000,
                    'method' => PaymentMethodEnum::SBP,
                    'commission_percents' => 5,
                    'variants' => [
                        [
                            'title' => 'Сбербанк',
                            'name' => 'sberbank',
                            'image' => '/assets/image/sberbank_logo.svg'
                        ],
                        [
                            'title' => 'Тинькофф',
                            'name' => 'tinkoff',
                            'image' => '/assets/image/tinkoff_logo.svg'
                        ],
                        [
                            'title' => 'Альфабанк',
                            'name' => 'alfabank',
                            'image' => '/assets/image/alfabank_logo.svg'
                        ],
                    ]
                ],
                [
                    'min' => 3200,
                    'max' => 300000,
                    'method' => PaymentMethodEnum::C2C,
                    'commission_percents' => 5,
                    'image' => '/assets/withdraw/bank-cards.png',
                ],
            ],
        ],
        PaymentProvidersEnum::USDT->value => [
            'class' => USDTPaymentProvider::class,
            'base_currency' => CurrenciesEnum::USDT,
            'provider' => PaymentProvidersEnum::USDT,
            'withdraw' => [
                [
                    'method' => PaymentMethodEnum::USDT,
                    'min' => 1000,
                    'hidden' => false,
                    'commission_percents' => 0,
                    'image' => '/assets/image/usdt.png',
                ]
            ]
        ],
        PaymentProvidersEnum::GOTHAM->value => [
            'class' => \App\Services\Payments\Providers\GothamPaymentsProvider::class,
            'base_currency' => CurrenciesEnum::RUB,
            'provider' => PaymentProvidersEnum::GOTHAM,
            'payment' => [
                [
                    'method' => PaymentMethodEnum::SBP,
                    'min' => 1000,
                    'max' => 30000,
                    'hidden' => true,
                    'image' => '/assets/withdraw/bank-cards.png',
                ],
                [
                    'method' => PaymentMethodEnum::C2C,
                    'min' => 1,
                    'max' => 999,
                    'hidden' => true,
                    'image' => '/assets/withdraw/bank-cards.png',
                ],
            ]
        ],
    ],
    'methods' => [
        PaymentMethodEnum::CRYPTOBOT->value => [
            'icon' => PaymentProviderIconsEnum::CRYPTOBOT,
            'title' => 'Cryptobot',
            'wallet_input_placeholder' => 'Telegram ID',
            'wallet_input_title' => 'Telegram ID. Узнать свой Telegram ID тут - @MyTidBot',
            'wallet_validation_rules' => [
                'required',
                'numeric',
            ],
            'wallet_validation_errors' => [
                'required' => 'Поле Telegram ID обязательно для заполнения',
                'numeric' => 'Поле Telegram ID должно содержать только цифры',
            ],
        ],
        PaymentMethodEnum::SBP_QR->value => [
            'icon' => PaymentProviderIconsEnum::SBP,
            'title' => 'СБП (QR)',
            'wallet_input_placeholder' => 'Номер телефона +7XXXXXXXXX',
            'wallet_input_title' => 'Номер телефона',
            'wallet_validation_rules' => [
                'required',
                'string',
                'regex:/^\+7\d{10}$/'
            ],
            'wallet_validation_errors' => [
                'required' => 'Укажите номер телефона.',
                'regex'    => 'Телефон должен быть в формате +7XXXXXXXXXX.',
            ],
        ],
        PaymentMethodEnum::SBP->value => [
            'icon' => PaymentProviderIconsEnum::SBP,
            'title' => 'СБП',
            'wallet_input_placeholder' => 'Номер телефона +7XXXXXXXXX',
            'wallet_input_title' => 'Номер телефона',
            'wallet_validation_rules' => [
                'required',
                'string',
                'regex:/^\+7\d{10}$/'
            ],
            'wallet_validation_errors' => [
                'required' => 'Укажите номер телефона.',
                'regex'    => 'Телефон должен быть в формате +7XXXXXXXXXX.',
            ],
        ],
        PaymentMethodEnum::FK->value => [
            'icon' => PaymentProviderIconsEnum::FK,
            'title' => 'FK Wallet',
            'wallet_input_title' => 'FK кошелек',
            'wallet_input_placeholder' => 'Номер кошелька FXXXXXXXXXXXXXXXX',
            'wallet_validation_rules' => [
                'required',
                'string',
                'regex:/^F[A-Za-z0-9]{15,17}$/'
            ],
            'wallet_validation_errors' => [
                'required' => 'Введите корректный кошелек',
                'string' => 'Введите корректный кошелек',
                'regex'    => 'Введите корректный кошелек',
            ],
        ],
        PaymentMethodEnum::USDT->value => [
            'icon' => PaymentProviderIconsEnum::USDT,
            'title' => 'USDT (TRC)',
            'wallet_input_title' => 'Кошелек usdt(trc20)',
            'wallet_input_placeholder' => 'Кошелек usdt(trc20)',
            'wallet_validation_rules' => [
                'required',
                'string',
                'regex:/^T[a-zA-Z0-9]{33}$/'
            ],
            'wallet_validation_errors' => [
                'required' => 'Введите корректный USDT TRC-20 кошелек',
                'string' => 'Введите корректный USDT TRC-20 кошелек',
                'regex'    => 'Введите корректный USDT TRC-20 кошелек',
            ],
        ],
        PaymentMethodEnum::C2C->value => [
            'icon' => PaymentProviderIconsEnum::CARD,
            'title' => 'Номер карты',
            'wallet_input_title' => 'Номер карты',
            'wallet_input_placeholder' => 'Номер карты',
            'wallet_validation_rules' => [
                'required',
                'string',
                'min:16',
                'max:20',
            ],
            'wallet_validation_errors' => [
                'required' => 'Введите корректный номер карты 1',
                'numeric' => 'Введите корректный номер карты 2',
                'min' => 'Введите корректный номер карты 3',
                'max' => 'Введите корректный номер карты 5',
            ],
        ],
    ]
];
