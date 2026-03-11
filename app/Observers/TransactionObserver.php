<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TransactionStatus;
use App\Jobs\EnsureGroupDeliveryJob;
use App\Models\Transaction;

class TransactionObserver
{
    public function updated(Transaction $transaction): void
    {
        if (! $transaction->wasChanged('status')) {
            return;
        }

        if ($transaction->status !== TransactionStatus::DIBAYAR) {
            return;
        }

        $groupId = $transaction->groupMember?->group_id;
        if (! $groupId) {
            return;
        }

        EnsureGroupDeliveryJob::dispatch($groupId);
    }
}
