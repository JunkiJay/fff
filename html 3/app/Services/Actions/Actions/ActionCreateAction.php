<?php

declare(strict_types=1);

namespace App\Services\Actions\Actions;

use App\Services\Actions\DTO\ActionCreateDTO;
use FKS\Actions\Action;
use Illuminate\Support\Str;

/**
 * @method static void handle(ActionCreateDTO $dto)
 */
class ActionCreateAction extends Action
{
    public function handle(ActionCreateDTO $dto): void
    {
        \App\Models\Action::create([
            'user_id' => $dto->userId,
            'action' => Str::limit($dto->action, 255),
            'balanceBefore' => $dto->balanceBefore,
            'balanceAfter' => $dto->balanceAfter,
            'additional_text' => $dto->additionalText
        ]);
    }
}