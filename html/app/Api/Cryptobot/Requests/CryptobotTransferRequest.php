<?php

declare(strict_types=1);

namespace App\Api\Cryptobot\Requests;

use Illuminate\Contracts\Support\Arrayable;

class CryptobotTransferRequest implements Arrayable
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var float|int
     */
    public $amount;
    /**
     * @var string
     */
    public $asset;
    /**
     * @var string
     */
    public $spendId;

    public function __construct(
        int $userId,
        $amount,
        string $asset,
        string $spendId
    ) {
        $this->userId = $userId;
        $this->amount = $amount;
        $this->asset = $asset;
        $this->spendId = $spendId;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'amount' => $this->amount,
            'asset' => $this->asset,
            'spend_id' => $this->spendId,
        ];
    }
}