<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Api\FK\FKApiClient;
use App\Api\GTX\GTXApiClient;
use App\Api\GTX\Requests\GTXPayRequest;
use App\Api\OnePlat\OnePlatApiClient;
use App\Api\OnePlat\Requests\OnePlatPayRequest;
use App\Models\User;
use App\Services\Payments\Actions\Payments\PayAction;
use App\Services\Payments\DTO\CreatePaymentDTO;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\PaymentProvidersEnum;
use App\Services\Payments\Facades\PaymentServiceFacade;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'test';

    public function handle(GTXApiClient $apiClient)
    {
        dd(
            $apiClient->pay(new GTXPayRequest(5000, '1111', '171332', PaymentMethodEnum::C2C))
        );
    }
}