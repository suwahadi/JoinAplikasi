<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\GroupMemberStatus;
use App\Enums\GroupStatus;
use App\Enums\PaymentChannel;
use App\Enums\TransactionStatus;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\ProductItem;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class OrderService
{
    /**
     * Temukan grup tersedia untuk product item dan buat order.
     * Idempoten: jika user sudah punya order aktif untuk product item ini,
     * kembalikan transaksi yang ada.
     */
    public function createOrderForProductItem(User $user, ProductItem $productItem): Transaction
    {
        $existing = $this->findExistingTransaction($user, $productItem->id);
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $productItem): Transaction {
            $group = Group::where('product_item_id', $productItem->id)
                ->where('status', GroupStatus::AVAILABLE->value)
                ->withCount(['members as active_count' => fn ($q) => $q->whereIn('status', [
                    GroupMemberStatus::PENDING->value,
                    GroupMemberStatus::CONFIRMED->value,
                    GroupMemberStatus::AKTIF->value,
                ])])
                ->orderByDesc('active_count')
                ->lockForUpdate()
                ->first();

            if (! $group) {
                throw new RuntimeException(
                    'Tidak ada slot grup yang tersedia untuk paket ini. Silakan hubungi admin.'
                );
            }

            return $this->doCreateOrder($user, $group, $productItem);
        });
    }

    /**
     * Gabung ke grup spesifik dan buat order.
     * Idempoten: jika user sudah punya order aktif di grup ini,
     * kembalikan transaksi yang ada.
     */
    public function createOrderForGroup(User $user, Group $group): Transaction
    {
        $existing = $this->findExistingTransactionForGroup($user, $group->id);
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $group): Transaction {
            $lockedGroup = Group::where('id', $group->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedGroup->status !== GroupStatus::AVAILABLE) {
                throw new RuntimeException('Grup ini sudah tidak tersedia.');
            }

            $productItem = $lockedGroup->productItem;

            return $this->doCreateOrder($user, $lockedGroup, $productItem);
        });
    }

    private function doCreateOrder(User $user, Group $group, ProductItem $productItem): Transaction
    {
        $activeMemberCount = GroupMember::where('group_id', $group->id)
            ->whereIn('status', [
                GroupMemberStatus::PENDING->value,
                GroupMemberStatus::CONFIRMED->value,
                GroupMemberStatus::AKTIF->value,
            ])
            ->count();

        if ($activeMemberCount >= $productItem->max_users) {
            throw new RuntimeException('Slot grup sudah penuh.');
        }

        $existingMember = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [
                GroupMemberStatus::PENDING->value,
                GroupMemberStatus::CONFIRMED->value,
                GroupMemberStatus::AKTIF->value,
            ])
            ->first();

        if ($existingMember?->transaction) {
            return $existingMember->transaction;
        }

        $groupMember = GroupMember::create([
            'group_id'  => $group->id,
            'user_id'   => $user->id,
            'status'    => GroupMemberStatus::PENDING->value,
        ]);

        if (($activeMemberCount + 1) >= $productItem->max_users) {
            $group->update(['status' => GroupStatus::FULL->value]);
        }

        $base = (int) $productItem->price_per_user;
        $discount = 0; // voucher/promo
        $fee = 3500;   // admin fee
        $total = max(0, $base - $discount + $fee);

        return Transaction::create([
            'uuid'               => (string) Str::uuid(),
            'group_member_id'    => $groupMember->id,
            'order_code'         => $this->generateUniqueOrderCode(),
            'payment_channel'    => PaymentChannel::QRIS->value,
            'amount'             => $total,
            'discount'           => $discount,
            'fee'                => $fee,
            'status'             => TransactionStatus::MENUNGGU_PEMBAYARAN->value,
            'payment_expired_at' => now()->addHours(24),
        ]);
    }

    private function findExistingTransaction(User $user, int $productItemId): ?Transaction
    {
        return Transaction::whereHas('groupMember', fn ($q) => $q
            ->where('user_id', $user->id)
            ->whereHas('group', fn ($q) => $q->where('product_item_id', $productItemId))
        )
        ->whereNotIn('status', [
            TransactionStatus::GAGAL->value,
            TransactionStatus::DIBATALKAN->value,
            TransactionStatus::KEDALUWARSA->value,
        ])
        ->latest()
        ->first();
    }

    private function findExistingTransactionForGroup(User $user, int $groupId): ?Transaction
    {
        return Transaction::whereHas('groupMember', fn ($q) => $q
            ->where('user_id', $user->id)
            ->where('group_id', $groupId)
        )
        ->whereNotIn('status', [
            TransactionStatus::GAGAL->value,
            TransactionStatus::DIBATALKAN->value,
            TransactionStatus::KEDALUWARSA->value,
        ])
        ->latest()
        ->first();
    }

    private function generateUniqueOrderCode(): string
    {
        for ($i = 0; $i < 10; $i++) {
            $code = 'ORD-' . strtoupper(Str::random(6)) . '-' . now()->format('ymd');
            if (! Transaction::where('order_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Gagal menghasilkan kode order unik.');
    }
}
