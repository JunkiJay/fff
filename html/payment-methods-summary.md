# Payment Methods and Configurations Summary

This document provides a comprehensive overview of all payment methods used in the application, based on the analysis of the following files:
- `app/Http/Controllers/PaymentController.php`
- `app/Http/Controllers/WithdrawController.php`
- `config/payment-providers.php`
- `app/Services/Payments/PaymentsService.php`
- `app/Enums/Payments/PaymentSystemEnum.php`

## Payment Architecture

The application uses a hybrid approach to payment processing:

1. **Modern Service-Based Architecture**:
   - Defined in `PaymentsService` and `PaymentSystemEnum`
   - Uses provider classes and configuration from `config/payment-providers.php`
   - Accessed via `PaymentServiceFacade`

2. **Controller-Based Implementation**:
   - Many payment methods are implemented directly in controllers
   - `PaymentController.php` handles deposits (6568 lines)
   - `WithdrawController.php` handles withdrawals (1244 lines)

## Active Payment Providers (Service-Based)

These providers are defined in `PaymentSystemEnum` and are fully integrated with the service architecture:

1. **FK (FK Wallet)**
   - **Type**: Withdrawal
   - **Currency**: RUB
   - **Min Amount**: 500
   - **Commission**: 5%
   - **Configuration**: 
     ```php
     PaymentsService::PAYMENT_PROVIDER_FK => [
         'system' => PaymentsService::PAYMENT_PROVIDER_FK,
         'class' => FKPaymentProvider::class,
         'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::RUB,
         'withdraw' => [
             'min' => 500,
             'currency' => \App\Services\Currencies\Enums\CurrenciesEnum::RUB,
             'comission' => 5,
             'icon' => '/assets/withdraw/bank-fk.png',
             'title' => 'FK Wallet',
             'hidden' => false,
         ]
     ]
     ```

2. **CRYPTOBOT**
   - **Type**: Deposit and Withdrawal
   - **Currency**: USDT
   - **Min Withdrawal**: 100
   - **Deposit Bonus**: 10%
   - **Configuration**:
     ```php
     PaymentsService::PAYMENT_PROVIDER_CRYPTOBOT => [
         'system' => PaymentsService::PAYMENT_PROVIDER_CRYPTOBOT,
         'class' => \App\Services\Payments\Providers\CryptobotPaymentProvider::class,
         'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::USDT,
         'api_token' => env('CRYPTOBOT_API_TOKEN', '341318:AA4jyFE4hQyeHYXHujOrdoTRuifVT2bLGaD'),
         'withdraw' => [
             'min' => 100,
             'icon' => '/assets/withdraw/bank-cryptobot.png',
             'hot' => true,
             'hidden' => false,
         ],
         'deposit' => [
             'bonus_persent' => 10
         ]
     ]
     ```

3. **BLVCKPAY**
   - **Type**: Deposit
   - **Currency**: RUB
   - **Min Amount**: 500
   - **Max Amount**: 300000
   - **Configuration**:
     ```php
     PaymentsService::PAYMENT_PROVIDER_BLVCKPAY => [
         'system' => PaymentsService::PAYMENT_PROVIDER_BLVCKPAY,
         'class' => BlvcpayPymentProvider::class,
         'base_currency' => \App\Services\Currencies\Enums\CurrenciesEnum::RUB,
         'deposit' => [
             'min' => 500,
             'max' => 300000,
             'min_deposits_count',
         ]
     ]
     ```

## Inactive Service-Based Providers

These providers are defined in `PaymentsService` but are commented out in the configuration:

1. **USDT**
2. **QIWI**
3. **ALFABANK**
4. **TINKOFF**
5. **SBERBANK**
6. **CARD**

## Payment Methods in PaymentController.php

The following payment methods are implemented directly in the PaymentController:

1. **Expay** - `handleExpay()`, `create()`
2. **Payou** - `createPayou()`, `callbackPayou()`
3. **1plat** - `create1plat()`, `callback1plat()`
4. **RoyalFinance** - `createRoyalFinance()`, `callbackRoyalFinance()`, `createRoyalFinanceNspk()`
5. **Nirvana** - `createNirvana()`, `callbackNirvana()`
6. **Transgran** - `createTransgran()`, `royalfinanceTransgran()`
7. **IDM** - `createIDM()`, `callbackIDM()`
8. **SpinPay** - `createSpinPay()`, `callbackSpinPay()`
9. **Yourpayment** - `handleYourpayment()`
10. **Aifory** - Multiple methods including SBP and Crypto variants:
    - `createAifory()`, `callbackAifory()`
    - `createAiforySBP()`, `getAiforySBPSingature()`
    - `createOrderAiforyCrypto()`, `callbackAiforyCrypto()`, `getAiforyCryptoSingature()`
11. **H2h** - `createOrderH2h()`
12. **NicePay** - `createNicePay()`, `callbackNicePay()`
13. **Paradise** - `createParadise()`, `callbackParadise()`
14. **Eightpay** - `createEightpay()`, `callbackEightpay()`
15. **Grow** - `createGrow()`, `callbackGrow()`
16. **Gtx** - `createGtx()`, `callbackGtx()`
17. **Cryptobot** - `createCryptobot()`, `callbackCryptobot()`
18. **Gotham** - `createGotham()`, `callbackGotham()`
19. **P2plab** - `createP2plab()`, `callbackP2plab()`, `calculateSignatureP2plab()`
20. **Repay** - `createRepay()`, `callbackRepay()`

## Withdrawal Methods in WithdrawController.php

The following withdrawal methods are implemented in the WithdrawController:

1. **Aifory SBP** - `aiforySBP()`
2. **Aifory Card** - `aiforyCard()`
3. **OnePay** - `sendOnePayPayoutRequest()`
4. **SpinPay** - `callbackSpinPay()`
5. **FKwallet** - `fkwalletHandle()`

## Currency Conversion

The application supports conversion between different currencies:
- RUB to USDT: `convertRubToUsdt()` in both controllers
- USDT to RUB: `convertUsdtToRub()` in WithdrawController
- Coin to RUB: `convertCoinToRub()` in PaymentController

## Conclusion

The application uses a large number of payment methods, with a mix of architectural approaches:

1. **Modern Service-Based Architecture**: FK, CRYPTOBOT, and BLVCKPAY are implemented using a service-based approach with proper configuration.

2. **Controller-Based Implementation**: Most payment methods are implemented directly in the controllers, which explains the large size of these files.

The application appears to be in a transition phase, moving from a controller-based implementation to a more modular service-based architecture. The newer payment methods (FK, CRYPTOBOT, BLVCKPAY) are implemented using the service-based approach, while the older methods remain in the controllers.