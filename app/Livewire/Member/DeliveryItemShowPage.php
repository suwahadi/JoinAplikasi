<?php

declare(strict_types=1);

namespace App\Livewire\Member;

use App\Models\DeliveryItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class DeliveryItemShowPage extends Component
{
    use AuthorizesRequests;

    public DeliveryItem $deliveryItem;

    public function mount(DeliveryItem $deliveryItem): void
    {
        $this->authorize('view', $deliveryItem);
        $this->deliveryItem = $deliveryItem->loadMissing(['credential.productItem', 'delivery.group', 'groupMember.user']);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.member.delivery-item-show-page')
            ->title('Detail Delivery');
    }
}
