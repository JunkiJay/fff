<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payments\CreateWithdrawRequest;
use App\Http\Requests\Api\V1\Withdraws\WithdrawSendToProviderRequest;
use App\Http\Requests\Api\V1\Withdraws\WithdrawsListRequest;
use App\Models\Withdraw;
use App\Services\Payments\Actions\Withdraws\WithdrawAction;
use App\Services\Payments\Actions\Withdraws\WithdrawSendToProviderAction;
use App\Services\Payments\PaymentsService;

class WithdrawsController extends Controller
{
    public function withdraw(CreateWithdrawRequest $request)
    {
        WithdrawAction::run($request->toDTO());
    }


    public function list(WithdrawsListRequest $request, PaymentsService $service)
    {
        $conditions = $request->getSearchConditions();
        return $service->getWithdrawsList($conditions)
            ->map(static fn (Withdraw $withdraw) => $withdraw->only($conditions->getAvailableFields()));
    }

    public function sendToProvider(WithdrawSendToProviderRequest $request)
    {
        return WithdrawSendToProviderAction::run((int) $request->id, $request->reason);
    }
}