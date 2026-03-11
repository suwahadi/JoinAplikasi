<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Group;
use App\Services\DeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class EnsureGroupDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $groupId,
    ) {}

    public function handle(DeliveryService $service): void
    {
        $lockKey = sprintf('delivery:group:%d', $this->groupId);
        Cache::lock($lockKey, 10)->block(10, function () use ($service): void {
            $group = Group::query()->find($this->groupId);
            if (!$group) {
                return;
            }

            $service->ensureGroupDelivery($group);
        });
    }
}
