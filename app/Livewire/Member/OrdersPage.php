<?php

declare(strict_types=1);

namespace App\Livewire\Member;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

class OrdersPage extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public ?string $status = null; // TransactionStatus value

    #[Url(as: 'from')]
    public ?string $dateFrom = null; // Y-m-d

    #[Url(as: 'to')]
    public ?string $dateTo = null; // Y-m-d

    public int $perPage = 20;
    public int $page = 1;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }

    public function resetPage(): void
    {
        $this->page = 1;
    }

    public function loadMore(): void
    {
        $this->page++;
    }

    protected function baseQuery()
    {
        $userId = Auth::id();

        $q = Transaction::query()
            ->with([
                'groupMember.group.productItem.product',
            ])
            ->whereHas('groupMember', fn ($qq) => $qq->where('user_id', $userId))
            ->latest('id');

        if ($this->status && in_array($this->status, array_column(TransactionStatus::cases(), 'value'), true)) {
            $q->where('status', $this->status);
        }

        if ($this->search !== '') {
            $term = trim($this->search);
            $q->where(function ($qq) use ($term) {
                $qq->where('order_code', 'like', "%$term%")
                   ->orWhereHas('groupMember.group.productItem.product', function ($pp) use ($term) {
                       $pp->where('name', 'like', "%$term%");
                   });
            });
        }

        if ($this->dateFrom) {
            $q->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $q->whereDate('created_at', '<=', $this->dateTo);
        }

        return $q;
    }

    public function render()
    {
        $query = $this->baseQuery();
        $orders = (clone $query)->take($this->perPage * $this->page)->get();
        // MySQL requires LIMIT when using OFFSET; add take(1) to make a valid query
        $hasMore = (clone $query)->skip($this->perPage * $this->page)->take(1)->exists();

        return view('livewire.member.orders-page', [
            'orders' => $orders,
            'hasMore' => $hasMore,
            'statuses' => TransactionStatus::cases(),
        ])->layout('layouts.marketing', ['title' => 'Pesanan Saya · ' . config('app.name')]);
    }
}
