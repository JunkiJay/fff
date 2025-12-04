<?php

declare(strict_types=1);

namespace App\Providers;

use App\Api\Blvckpay\BlvckpayApiClient;
use App\Api\Cryptobot\CryptobotApiClient;
use App\Api\Exotic\ExoticApiClient;
use App\Api\Expay\ExpayApiClient;
use App\Api\FK\FKApiClient;
use App\Api\Gotham\GothamApiClient;
use App\Api\GTX\GTXApiClient;
use App\Api\OnePayment\OnePaymentsApiClient;
use App\Api\OnePlat\OnePlatApiClient;
use App\Api\Paradise\ParadiseApiClient;
use App\Api\PSPay\PSPayApiClient;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class ApiClientsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BlvckpayApiClient::class, function () {
            return new BlvckpayApiClient(
                new Client([
                    'base_uri' => config('api-clients.blvckpay.base_url'),
                    'timeout' => 10.0,
                ])
            );
        });
        $this->app->singleton(FKApiClient::class, function () {
            return new FKApiClient(
                new Client([
                    'base_uri' => config('api-clients.fk.base_url'),
                    'timeout' => 10.0,
                ])
            );
        });
        $this->app->singleton(CryptobotApiClient::class, function () {
            return new CryptobotApiClient(
                new Client([
                    'base_uri' => config('api-clients.cryptobot.base_url'),
                    'timeout' => 10.0,
                ])
            );
        });
        $this->app->singleton(ParadiseApiClient::class, function () {
            return new ParadiseApiClient(
                new Client([
                    'base_uri' => config('api-clients.paradise.base_url'),
                    'timeout' => 10.0,
                ])
            );
        });
        $this->app->singleton(PSPayApiClient::class, function () {
            return new PSPayApiClient(
                new Client([
                    'base_uri' => config('api-clients.pspay.base_url'),
                    'timeout' => 10.0,
                ])
            );
        });
        $this->app->singleton(ExpayApiClient::class, function () {
            return new ExpayApiClient(
                new Client([
                    'base_uri' => config('api-clients.expay.base_url'),
                    'timeout' => 10.0,
                ])
            );
        });
        $this->app->singleton(ExoticApiClient::class, function () {
            return new ExoticApiClient(
                new Client([
                    'base_uri' => config('api-clients.expay.base_url'),
                    'timeout' => 10.0,
                ])
            );
        });
        $this->app->singleton(OnePlatApiClient::class, function () {
            return new OnePlatApiClient(
                new Client([
                    'base_uri' => config('api-clients.1plat.base_url'),
                    'timeout' => 10.0,
                ])
            );
        });
        $this->app->singleton(OnePaymentsApiClient::class, function () {
            return new OnePaymentsApiClient(
                new Client([
                    'base_uri' => config('api-clients.onepayments.base_url'),
                    'timeout' => 10.0,
                    'headers' => [
                        'Authorization' => 'Bearer ' . config('api-clients.onepayments.api_key'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ]
                ])
            );
        });
        $this->app->singleton(GothamApiClient::class, function () {
            return new GothamApiClient(
                new Client([
                    'base_uri' => config('api-clients.gotham.base_url'),
                    'timeout' => 10.0,
                    'headers' => [
                        'Authorization' => 'Bearer ' . config('api-clients.gotham.api_key'),
                        'X-Username' => config('api-clients.gotham.user_name'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ]
                ])
            );
        });
        $this->app->singleton(GTXApiClient::class, function () {
            return new GTXApiClient(
                new Client([
                    'base_uri' => config('api-clients.gtx.base_url'),
                    'timeout' => 10.0,
                    'headers' => [
                        'Authorization' => 'Bearer ' . config('api-clients.gtx.api_token'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ]
                ])
            );
        });
    }
}