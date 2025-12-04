<?php

declare(strict_types=1);

namespace App\Console\Commands\Slots;

use App\Models\B2bSlot;
use App\Models\MobuleSlot;
use App\Models\Slots\Slot;
use App\Services\Slots\Facades\SlotServiceFacade;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SyncSlotsCommand extends Command
{
    protected $signature = 'slots:sync';
    protected $description = 'Sync slots to common table';

    public function handle(): void
    {
        MobuleSlot::query()
            ->chunkById(100, function (Collection $items) {
                /** @var MobuleSlot $item */
                foreach ($items as $item) {
                    Slot::query()
                        ->updateOrCreate(
                            [
                                'id' => $item->id,
                                'title' => $item->title,
                                'provider' => $item->provider,
                            ],
                            [
                                'image' => SlotServiceFacade::getImageByName($item->title),
                                'show' => $item->show,
                            ]
                        );
                }
            });
        B2bSlot::query()
            ->chunkById(100, function (Collection $items) {
                /** @var B2bSlot $item */
                foreach ($items as $item) {
                    Slot::query()
                        ->updateOrCreate(
                            [
                                'id' => $item->id,
                                'title' => str_replace('.gmae', '', $item->gm_url),
                                'provider' => $item->gr_title,
                            ],
                            [
                                'image' => SlotServiceFacade::getImageByName($item->gm_url),
                                'show' => $item->show,
                            ]
                        );
                }
            });
    }
}