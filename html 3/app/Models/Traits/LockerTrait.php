<?php

declare(strict_types=1);

namespace App\Models\Traits;

trait LockerTrait
{
    public function canCreate(): true
    {
        return true;
    }

    public function handleCreating(): void
    {

    }

    public function handleCreated(): void
    {

    }

    public function handleUpdated(): void
    {

    }
}