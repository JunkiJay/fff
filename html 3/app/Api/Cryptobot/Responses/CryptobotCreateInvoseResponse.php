<?php

declare(strict_types=1);

namespace App\Api\Cryptobot\Responses;

class CryptobotCreateInvoseResponse
{
    public string $status;
    public int $invoiceId;
    public string $url;

    public function __construct(string $status, int $invoiceId, string $url)
    {
        $this->status = $status;
        $this->invoiceId = $invoiceId;
        $this->url = $url;
    }
}