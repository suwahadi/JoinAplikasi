<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\EnsureGroupDeliveryJob;
use App\Models\Group;

class GroupObserver
{
    public function created(Group $group): void
    {
        EnsureGroupDeliveryJob::dispatch($group->id);
    }

    public function updated(Group $group): void
    {
        EnsureGroupDeliveryJob::dispatch($group->id);
    }

    public function deleted(Group $group): void
    {
        EnsureGroupDeliveryJob::dispatch($group->id);
    }
}
