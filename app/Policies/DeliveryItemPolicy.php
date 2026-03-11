<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DeliveryItem;
use App\Models\User;

class DeliveryItemPolicy
{
    public function view(User $user, DeliveryItem $item): bool
    {
        // Hanya pemilik (anggota grup) yang boleh melihat, dan item harus visible
        if (! $item->relationLoaded('groupMember')) {
            $item->loadMissing(['groupMember', 'delivery']);
        }

        $ownerId = $item->groupMember?->user_id;
        if ($ownerId !== $user->id) {
            return false;
        }

        if (! $item->visible) {
            return false;
        }

        // Jika ada expiry di delivery, pastikan belum kadaluarsa
        $delivery = $item->delivery;
        if ($delivery && $delivery->expires_at && now()->greaterThan($delivery->expires_at)) {
            return false;
        }

        return true;
    }
}
