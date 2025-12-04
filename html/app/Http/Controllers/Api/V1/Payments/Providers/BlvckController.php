<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Payments\Providers;

use App\Enums\Payments\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Payments\BlvckDepositWebhookRequest;
use App\Services\Payments\Actions\Payments\PaymentCompleteAction;
use App\Services\Payments\Actions\Payments\PaymentFailAction;
use App\Services\Payments\Facades\PaymentServiceFacade;

class BlvckController extends Controller
{
    public function depositWebhook(BlvckDepositWebhookRequest $request)
    {
        \Log::debug('BLVCPAY', [$request->all(), $request->ip()]);

//        if (!in_array($request->ip(), ['31.130.151.189', '45.10.240.195'])) {
//            return response()->json(['error' => 'Invalid IP'], 400);
//        }

        $payment = PaymentServiceFacade::findDepositByExternalId($request->order_id);

        if ($payment->status === PaymentStatusEnum::SUCCESS) {
            return response()->json(['error' => 'Payment already completed'], 400);
        }

        if ($payment === null) {
            \Log::error('BLVCPAY Payment Not Found', $request->all());

            return response()->json(['error' => 'Payment not found'], 404);
        }

        if ($request->status === 'Paid') {
            PaymentCompleteAction::run($payment);
        } else {
            PaymentFailAction::run($payment);
        }
    }
}