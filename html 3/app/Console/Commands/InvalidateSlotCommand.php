<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\B2bSlot;
use App\Models\MobuleSlot;
use App\Models\Slots\Slot;
use App\Services\Slots\SlotsService;
use Illuminate\Console\Command;

class InvalidateSlotCommand extends Command
{
    protected $signature = 'slot:invalidate';

    public function handle(SlotsService $service)
    {
        $counts = 0;

        Slot::query()
            ->orderBy('id')
            ->chunkById(1000, function ($items) use ($service, &$counts) {
                foreach ($items as $item) {
                    $test = $service->getImage($item);
                    if ($test == null) {
                        $counts++;
                        $this->output->writeln('Invalidating slot: ' . $item->id);
                    }
                }
            });
        B2bSlot::query()
            ->orderBy('id')
            ->chunkById(1000, function ($items) use ($service, &$counts) {
                foreach ($items as $item) {
                    $test = $service->getImage($item);
                    if ($test == null) {
                        $counts++;
                        $this->output->writeln('Invalidating b2b slot: ' . $item->id . ' ' . $item->gm_url);
                    }
                }
            });
        MobuleSlot::query()
            ->orderBy('id')
            ->chunkById(1000, function ($items) use ($service, &$counts) {
                foreach ($items as $item) {
                    $test = $service->getImage($item);
                    if ($test == null) {
                        $counts++;
                        $this->output->writeln('Invalidating module slot: ' . $item->id . ' ' . $item->gm_url);
                    }
                }
            });

        $this->output->writeln('Invalidated ' . $counts . ' slots.');

    }
}