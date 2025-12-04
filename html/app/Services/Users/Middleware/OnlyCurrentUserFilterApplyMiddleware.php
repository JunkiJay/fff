<?php

declare(strict_types=1);

namespace App\Services\Users\Middleware;

use FKS\Search\Http\Middlewares\FilterApplyMiddleware;
use FKS\Search\ValueObjects\Conditions\Condition;
use FKS\Search\ValueObjects\Conditions\ContainsCondition;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class OnlyCurrentUserFilterApplyMiddleware extends FilterApplyMiddleware
{
    public function shouldBeApplied(Request $request): bool
    {
        return true;
    }

    public function getCondition(Request $request): Condition
    {
        if ($request->user()?->id === null) {
            throw new AuthenticationException();
        }

        return ContainsCondition::make(
            'user_id',
            [$request->user()->id],
            true,
            ContainsCondition::TYPE_INTEGER_IN_ARRAY
        );
    }
}