<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\TransactionStatus;
use App\Models\Credential;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Group;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    /**
     * Idempoten: memastikan delivery aktif dibuat/diupdate saat kuorum tercapai.
     */
    public function ensureGroupDelivery(Group $group): void
    {
        $group->loadMissing(['productItem.product', 'members']);

        $memberIds = $group->members()->pluck('id');

        $paidMemberIds = Transaction::query()
            ->whereIn('group_member_id', $memberIds)
            ->where('status', TransactionStatus::DIBAYAR)
            ->pluck('group_member_id')
            ->unique();

        $paidCount = $paidMemberIds->count();
        $capacity = (int) optional($group->productItem)->max_users;

        DB::transaction(function () use ($group, $paidMemberIds, $paidCount, $capacity): void {
            $activeDelivery = Delivery::query()
                ->where('group_id', $group->id)
                ->where('status', DeliveryStatus::ACTIVE)
                ->first();

            if ($paidCount < $capacity && !$activeDelivery) {
                // Belum memenuhi kuorum dan belum ada delivery aktif: tidak melakukan apa-apa.
                return;
            }

            $credential = Credential::query()
                ->where('product_item_id', $group->product_item_id)
                ->latest('id')
                ->first();

            if (!$activeDelivery) {
                $expiresAt = null;
                $duration = optional(optional($group->productItem)->product)->duration;
                if (is_numeric($duration) && (int) $duration > 0) {
                    $expiresAt = CarbonImmutable::now()->addDays((int) $duration);
                }

                $activeDelivery = Delivery::query()->create([
                    'group_id' => $group->id,
                    'status' => DeliveryStatus::ACTIVE,
                    'activated_at' => now(),
                    'expires_at' => $expiresAt,
                ]);
            }

            // Sinkronisasi delivery_items untuk semua member yang sudah dibayar
            foreach ($paidMemberIds as $memberId) {
                /** @var DeliveryItem $item */
                $item = DeliveryItem::query()->firstOrCreate(
                    [
                        'delivery_id' => $activeDelivery->id,
                        'group_member_id' => $memberId,
                    ],
                    [
                        'visible' => true,
                        'delivered_at' => now(),
                        'credential_id' => $credential?->id,
                    ],
                );

                // Pastikan visible & credential terisi bila sebelumnya belum
                if (!$item->visible || (!$item->credential_id && $credential)) {
                    $item->fill([
                        'visible' => true,
                        'credential_id' => $item->credential_id ?: $credential?->id,
                        'delivered_at' => $item->delivered_at ?: now(),
                    ])->save();
                }
            }
        });
    }
}
