<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PaymenProviders\CreatePaymentRequest;
use App\Http\Requests\Api\V1\Payments\PaymentsListRequest;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\Actions\Payments\PayAction;
use App\Services\Payments\Actions\Payments\PayCallbackAction;
use App\Services\Payments\PaymentsService;
use App\Services\Payments\ValueObjects\PaymentSuccessResult;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function pay(CreatePaymentRequest $request)
    {
        PayAction::run($request->toDTO());
    }

    public function paymentsMethods(int|string $userId, PaymentsService $paymentsService)
    {
        return $paymentsService->paymentProviders(User::find($userId));
    }

    public function list(PaymentsListRequest $request, PaymentsService $paymentsService)
    {
        $conditions = $request->getSearchConditions();
        return $paymentsService->getPaymentsList($request->getSearchConditions())
            ->map(static fn(Payment $withdraw) => $withdraw->only($conditions->getAvailableFields()));
    }

    public function callback(string $secret, Request $request, PaymentsService $paymentsService)
    {
        $result = PayCallbackAction::run($secret, $request->toArray());

        if ($result instanceof PaymentSuccessResult) {
            return response()->json(['success' => true]);
        } else {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => $result->error
                ]
            ]);
        }
    }
}