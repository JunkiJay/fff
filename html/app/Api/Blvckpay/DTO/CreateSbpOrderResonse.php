<?php

declare(strict_types=1);

namespace App\Api\Blvckpay\DTO;

class CreateSbpOrderResonse
{
    public $status;
    public $url;
    public $orderId;

    public function __construct(string $status, string $url, string $orderId)
    {
        $this->status = $status;
        $this->url = $url;
        $this->orderId = $orderId;
    }
}