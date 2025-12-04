<?php

declare(strict_types=1);

namespace App\Api\Exotic;

use FKS\Api\ApiClient;

class ExoticApiClient extends ApiClient
{
    public function createPayment(): void
    {
        $this->post('create_pay_in');
    }
}