<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\PaymenProviders;

use App\Services\Payments\DTO\CreatePaymentDTO;
use FKS\Serializer\SerializerFacade;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'method' => 'required|string|max:255',
            'system' => [
                'required',
                'payment_method',
            ],
            'code' => [
                'string',
                'exists:promocodes,name',
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'code' => [
                'exists' => 'Промокод не найден'
            ]
        ];
    }

    public function toDTO()
    {
        return SerializerFacade::deserializeFromArray($this->toArray(), CreatePaymentDTO::class);
    }
}