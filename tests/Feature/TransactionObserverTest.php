<?php

declare(strict_types=1);

use App\Enums\TransactionStatus;
use App\Jobs\EnsureGroupDeliveryJob;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('dispatches EnsureGroupDeliveryJob when transaction status becomes DIBAYAR', function () {
    Bus::fake();

    $product = Product::query()->create([
        'name' => 'Prod',
        'slug' => 'prod',
        'duration' => 7,
        'is_active' => true,
    ]);

    $item = ProductItem::query()->create([
        'product_id' => $product->id,
        'name' => 'Item',
        'slug' => 'item',
        'price_per_user' => 1000,
        'max_users' => 3,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $group = Group::query()->create([
        'product_item_id' => $item->id,
        'owner_id' => null,
        'name' => 'G1',
        'status' => null,
        'pre_order' => false,
    ]);

    $member = GroupMember::query()->create([
        'group_id' => $group->id,
        'user_id' => 1,
        'status' => 'aktif',
        'joined_at' => now(),
    ]);

    $tx = Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'group_member_id' => $member->id,
        'order_code' => 'ORD-'.$member->id,
        'amount' => 1000,
        'status' => TransactionStatus::MENUNGGU,
    ]);

    // Update to paid triggers observer
    $tx->update(['status' => TransactionStatus::DIBAYAR]);

    Bus::assertDispatched(EnsureGroupDeliveryJob::class, function ($job) use ($group) {
        return (int) $job->groupId === (int) $group->id;
    });
});
