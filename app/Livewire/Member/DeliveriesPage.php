<?php

declare(strict_types=1);

namespace App\Livewire\Member;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeliveriesPage extends Component
{
    public function render()
    {
        $userId = Auth::id();

        /** @var LengthAwarePaginator $items */
        $items = DeliveryItem::query()
            ->with(['delivery.group.productItem.product', 'credential.productItem', 'groupMember'])
            ->whereHas('groupMember', fn ($q) => $q->where('user_id', $userId))
            ->where('visible', true)
            ->whereHas('delivery', function ($q) {
                $q->where('status', DeliveryStatus::ACTIVE)
                  ->where(function ($q2) {
                      $q2->whereNull('expires_at')->orWhere('expires_at', '>', now());
                  });
            })
            ->latest('id')
            ->paginate(10);

        return view('livewire.member.deliveries-page', [
            'items' => $items,
        ])->layout('layouts.marketing', ['title' => 'Delivery Saya · ' . config('app.name')]);
    }
}
