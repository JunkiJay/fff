<?php

declare(strict_types=1);

namespace App\Api\Cryptobot\Responses;

class CryptobotTransferResponse
{
    /**
     * @var string
     */
    public $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function isSuccess(): bool
    {
        return $this->status === 'completed';
    }
}