<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Withdraws;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawSendToProviderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:withdraws,id',
            'reason' => 'string|max:255'
        ];
    }
}