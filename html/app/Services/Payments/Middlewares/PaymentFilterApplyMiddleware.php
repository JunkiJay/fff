<?php

declare(strict_types=1);

namespace App\Services\Payments\Middlewares;

use App\Facades\AbilityFacade;
use FKS\Search\Http\Middlewares\FilterApplyMiddleware;
use FKS\Search\ValueObjects\Conditions\Condition;
use FKS\Search\ValueObjects\Conditions\ContainsCondition;
use Illuminate\Http\Request;

class PaymentFilterApplyMiddleware extends FilterApplyMiddleware
{
    public function shouldBeApplied(Request $request): bool
    {
        return AbilityFacade::canSeeAllPayments();
    }

    public function getCondition(string $name, Request $request): Condition
    {
        return ContainsCondition::make(
            'user_id',
            [$request->user()->id],
            true,
            ContainsCondition::TYPE_INTEGER_IN_ARRAY
        );
    }
}