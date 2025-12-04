<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Payments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlvckDepositWebhookRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'status' => [
                'required',
                'string',
                Rule::in(['Paid', 'Expired'])
            ],
        ];
    }
}