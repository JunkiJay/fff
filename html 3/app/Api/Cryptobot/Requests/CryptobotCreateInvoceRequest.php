<?php

declare(strict_types=1);

namespace App\Api\Cryptobot\Requests;

use Illuminate\Contracts\Support\Arrayable;

class CryptobotCreateInvoceRequest implements Arrayable
{
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
        $amount,
        string $asset,
        string $spendId
    ) {
        $this->amount = $amount;
        $this->asset = $asset;
        $this->spendId = $spendId;
    }

    public function toArray(): array
    {
        return [
            'currency_type' => 'fiat',
            'fiat' => "RUB",
            'asset' => $this->asset,
            'amount' => $this->amount,
            'comment' => $this->spendId,
        ];
    }
}