<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Payments;

use App\Services\Payments\DTO\CreateWithdrawDTO;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\PaymentProvidersEnum;
use FKS\Serializer\SerializerFacade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWithdrawRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'provider' => [
                'required',
                Rule::enum(PaymentProvidersEnum::class)
            ],
            'method' => [
                'required',
                Rule::enum(PaymentMethodEnum::class),
            ],
            'amount' => 'required|numeric',
            'wallet' => 'string',
        ];
    }

    public function toDTO()
    {
        return SerializerFacade::deserializeFromArray($this->toArray(), CreateWithdrawDTO::class);
    }
}