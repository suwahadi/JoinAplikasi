<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Livewire\Component;

class DashboardPage extends Component
{
    public function render()
    {
        $user = auth()->user();

        $recentTransactions = Transaction::whereHas(
            'groupMember',
            fn ($q) => $q->where('user_id', $user->id)
        )
            ->with('groupMember.group.productItem.product')
            ->latest()
            ->limit(8)
            ->get();

        $totalCount = Transaction::whereHas(
            'groupMember',
            fn ($q) => $q->where('user_id', $user->id)
        )->count();

        $paidCount = Transaction::whereHas(
            'groupMember',
            fn ($q) => $q->where('user_id', $user->id)
        )->where('status', TransactionStatus::DIBAYAR)->count();

        $totalSpent = Transaction::whereHas(
            'groupMember',
            fn ($q) => $q->where('user_id', $user->id)
        )->where('status', TransactionStatus::DIBAYAR)->sum('amount');

        return view('livewire.pages.dashboard-page', [
            'transactions' => $recentTransactions,
            'totalCount'   => $totalCount,
            'paidCount'    => $paidCount,
            'totalSpent'   => $totalSpent,
        ])->layout('layouts.marketing', ['title' => 'Dashboard · ' . config('app.name')]);
    }
}
