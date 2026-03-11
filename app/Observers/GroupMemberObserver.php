<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\EnsureGroupDeliveryJob;
use App\Models\GroupMember;

class GroupMemberObserver
{
    public function created(GroupMember $member): void
    {
        EnsureGroupDeliveryJob::dispatch($member->group_id);
    }

    public function updated(GroupMember $member): void
    {
        EnsureGroupDeliveryJob::dispatch($member->group_id);
    }

    public function deleted(GroupMember $member): void
    {
        EnsureGroupDeliveryJob::dispatch($member->group_id);
    }
}
