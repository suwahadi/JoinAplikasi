<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\GroupMemberStatus;
use App\Enums\GroupStatus;
use App\Enums\PaymentChannel;
use App\Enums\TransactionStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\PaymentNotification;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class JoinAplikasiSeeder extends Seeder
{
    private array $productItems = [];

    private array $groups = [];

    private array $groupMembers = [];

    public function run(): void
    {
        $users = $this->seedUsers();
        $this->seedCatalog();
        $this->seedPromotions();
        $this->seedGroups($users);
        $this->seedTransactions();
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $definitions = [
            'admin' => [
                'name' => 'Admin JoinAplikasi',
                'email' => 'admin@joinaplikasi.local',
                'phone' => '081100110011',
                'role' => UserRole::ADMIN->value,
            ],
            'owner' => [
                'name' => 'Dwi Owner',
                'email' => 'owner@joinaplikasi.local',
                'phone' => '081200220022',
                'role' => UserRole::USER->value,
            ],
            'memberA' => [
                'name' => 'Sari Anggota',
                'email' => 'sari@joinaplikasi.local',
                'phone' => '081300330033',
                'role' => UserRole::USER->value,
            ],
            'memberB' => [
                'name' => 'Andi Anggota',
                'email' => 'andi@joinaplikasi.local',
                'phone' => '081400440044',
                'role' => UserRole::USER->value,
            ],
        ];

        $users = [];

        foreach ($definitions as $key => $data) {
            $users[$key] = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'role' => $data['role'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        return $users;
    }

    private function seedCatalog(): void
    {
        $categorySlugs = [
            'streaming-musik' => 'Streaming Musik',
            'streaming-film' => 'Streaming Film',
            'ai-tools' => 'AI Tools',
        ];

        $categories = [];
        foreach ($categorySlugs as $slug => $name) {
            $categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        $productDefinitions = [
            [
                'name' => 'Spotify Premium',
                'slug' => 'spotify-premium',
                'description' => 'Langganan Spotify Premium Family via grup patungan.',
                'image' => 'products/spotify.png',
                'duration' => 30,
                'categories' => ['streaming-musik'],
                'items' => [
                    [
                        'key' => 'spotify-3-user',
                        'name' => 'Spotify 3 Pengguna',
                        'price_per_user' => 19000,
                        'max_users' => 3,
                        'sort_order' => 1,
                    ],
                    [
                        'key' => 'spotify-5-user',
                        'name' => 'Spotify 5 Pengguna',
                        'price_per_user' => 17000,
                        'max_users' => 5,
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name' => 'Netflix 4K',
                'slug' => 'netflix-4k',
                'description' => 'Patungan Netflix Ultra HD untuk keluarga dan teman.',
                'image' => 'products/netflix.png',
                'duration' => 30,
                'categories' => ['streaming-film'],
                'items' => [
                    [
                        'key' => 'netflix-4-user',
                        'name' => 'Netflix Premium 4 Pengguna',
                        'price_per_user' => 45000,
                        'max_users' => 4,
                        'sort_order' => 1,
                    ],
                ],
            ],
            [
                'name' => 'Grok AI',
                'slug' => 'grok-ai',
                'description' => 'Akses Grok AI Team Plan via JoinAplikasi.',
                'image' => 'products/grok.png',
                'duration' => 31,
                'categories' => ['ai-tools'],
                'items' => [
                    [
                        'key' => 'grok-5-user',
                        'name' => 'Grok Teams 5 Pengguna',
                        'price_per_user' => 120000,
                        'max_users' => 5,
                        'sort_order' => 1,
                    ],
                ],
            ],
        ];

        foreach ($productDefinitions as $productData) {
            $product = Product::updateOrCreate(
                ['slug' => $productData['slug']],
                [
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'image' => $productData['image'],
                    'duration' => $productData['duration'],
                    'is_active' => true,
                ]
            );

            $categoryIds = collect($productData['categories'])
                ->map(fn ($slug) => $categories[$slug]->id)
                ->all();
            $product->categories()->sync($categoryIds);

            foreach ($productData['items'] as $itemData) {
                $item = ProductItem::updateOrCreate(
                    ['product_id' => $product->id, 'name' => $itemData['name']],
                    [
                        'slug' => Str::slug($product->slug . '-' . $itemData['key']),
                        'price_per_user' => $itemData['price_per_user'],
                        'max_users' => $itemData['max_users'],
                        'sort_order' => $itemData['sort_order'],
                        'is_active' => true,
                    ]
                );

                $this->productItems[$itemData['key']] = $item;
            }
        }
    }

    private function seedPromotions(): void
    {
        $promotionDefinitions = [
            [
                'code' => 'SPOTIFYHEMAT',
                'discount_amount' => 10000,
                'discount_percent' => null,
                'valid_until' => Carbon::now()->addDays(14),
                'items' => ['spotify-5-user'],
            ],
            [
                'code' => 'AIWEEKEND25',
                'discount_amount' => 0,
                'discount_percent' => 25.0,
                'valid_until' => Carbon::now()->addDays(10),
                'items' => ['grok-5-user'],
            ],
        ];

        foreach ($promotionDefinitions as $promoData) {
            $promotion = Promotion::updateOrCreate(
                ['code' => $promoData['code']],
                [
                    'discount_amount' => $promoData['discount_amount'],
                    'discount_percent' => $promoData['discount_percent'],
                    'valid_until' => $promoData['valid_until'],
                ]
            );

            $itemIds = collect($promoData['items'])
                ->filter(fn ($key) => isset($this->productItems[$key]))
                ->map(fn ($key) => $this->productItems[$key]->id)
                ->all();

            if ($itemIds !== []) {
                $promotion->productItems()->sync($itemIds);
            }
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedGroups(array $users): void
    {
        $groupDefinitions = [
            [
                'key' => 'spotify_elite',
                'product_item_key' => 'spotify-3-user',
                'owner' => 'owner',
                'name' => 'Spotify Elite Squad',
                'status' => GroupStatus::AVAILABLE->value,
                'pre_order' => false,
                'members' => [
                    [
                        'user' => 'owner',
                        'status' => GroupMemberStatus::AKTIF->value,
                        'joined_at' => Carbon::now()->subDays(2),
                        'reference' => 'spotify_owner',
                    ],
                    [
                        'user' => 'memberA',
                        'status' => GroupMemberStatus::CONFIRMED->value,
                        'joined_at' => Carbon::now()->subDay(),
                        'reference' => 'spotify_memberA',
                    ],
                ],
            ],
            [
                'key' => 'netflix_binge',
                'product_item_key' => 'netflix-4-user',
                'owner' => 'admin',
                'name' => 'Netflix Binge Watchers',
                'status' => GroupStatus::FULL->value,
                'pre_order' => false,
                'members' => [
                    [
                        'user' => 'admin',
                        'status' => GroupMemberStatus::AKTIF->value,
                        'joined_at' => Carbon::now()->subDays(5),
                        'reference' => 'netflix_admin',
                    ],
                    [
                        'user' => 'owner',
                        'status' => GroupMemberStatus::AKTIF->value,
                        'joined_at' => Carbon::now()->subDays(4),
                        'reference' => 'netflix_owner',
                    ],
                    [
                        'user' => 'memberB',
                        'status' => GroupMemberStatus::PENDING->value,
                        'joined_at' => null,
                        'reference' => 'netflix_memberB',
                    ],
                ],
            ],
            [
                'key' => 'grok_preorder',
                'product_item_key' => 'grok-5-user',
                'owner' => 'memberA',
                'name' => 'Grok Early Access',
                'status' => GroupStatus::AVAILABLE->value,
                'pre_order' => true,
                'members' => [
                    [
                        'user' => 'memberA',
                        'status' => GroupMemberStatus::AKTIF->value,
                        'joined_at' => Carbon::now()->subDays(1),
                        'reference' => 'grok_memberA',
                    ],
                    [
                        'user' => 'memberB',
                        'status' => GroupMemberStatus::CONFIRMED->value,
                        'joined_at' => null,
                        'reference' => 'grok_memberB',
                    ],
                ],
            ],
        ];

        foreach ($groupDefinitions as $groupData) {
            $productItem = $this->productItems[$groupData['product_item_key']] ?? null;
            $owner = $users[$groupData['owner']] ?? null;

            if (! $productItem || ! $owner) {
                continue;
            }

            $group = Group::updateOrCreate(
                ['name' => $groupData['name']],
                [
                    'product_item_id' => $productItem->id,
                    'owner_id' => $owner->id,
                    'status' => $groupData['status'],
                    'pre_order' => $groupData['pre_order'],
                ]
            );

            $this->groups[$groupData['key']] = $group;

            foreach ($groupData['members'] as $memberData) {
                $user = $users[$memberData['user']] ?? null;
                if (! $user) {
                    continue;
                }

                $member = GroupMember::updateOrCreate(
                    [
                        'group_id' => $group->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'status' => $memberData['status'],
                        'joined_at' => $memberData['joined_at'],
                    ]
                );

                if (! empty($memberData['reference'])) {
                    $this->groupMembers[$memberData['reference']] = $member;
                }
            }
        }
    }

    private function seedTransactions(): void
    {
        $transactionDefinitions = [
            [
                'reference' => 'trx_spotify_memberA',
                'member_ref' => 'spotify_memberA',
                'order_code' => 'TRX-SPOTIFY-001',
                'midtrans_order_id' => 'MID-SPOTIFY-001',
                'payment_channel' => PaymentChannel::BCA_VA->value,
                'payment_reference' => '9881234567',
                'amount' => $this->productItems['spotify-3-user']->price_per_user ?? 19000,
                'status' => TransactionStatus::MENUNGGU_PEMBAYARAN->value,
                'payment_expired_at' => Carbon::now()->addDay(),
                'midtrans_status' => 'pending',
            ],
            [
                'reference' => 'trx_grok_memberB',
                'member_ref' => 'grok_memberB',
                'order_code' => 'TRX-GROK-002',
                'midtrans_order_id' => 'MID-GROK-002',
                'payment_channel' => PaymentChannel::QRIS->value,
                'payment_reference' => 'QR-99887766',
                'amount' => $this->productItems['grok-5-user']->price_per_user ?? 120000,
                'status' => TransactionStatus::DIBAYAR->value,
                'payment_expired_at' => Carbon::now()->addHours(6),
                'paid_at' => Carbon::now()->subHours(2),
                'midtrans_status' => 'settlement',
            ],
        ];

        foreach ($transactionDefinitions as $definition) {
            $member = $this->groupMembers[$definition['member_ref']] ?? null;
            if (! $member) {
                continue;
            }

            $transaction = Transaction::firstOrNew(['order_code' => $definition['order_code']]);

            if (! $transaction->exists) {
                $transaction->uuid = (string) Str::uuid();
            }

            $transaction->forceFill([
                'group_member_id' => $member->id,
                'midtrans_order_id' => $definition['midtrans_order_id'],
                'midtrans_transaction_id' => Str::upper(Str::random(12)),
                'midtrans_payment_type' => $definition['payment_channel'],
                'midtrans_transaction_status' => $definition['midtrans_status'],
                'midtrans_fraud_status' => 'accept',
                'midtrans_status_code' => '200',
                'midtrans_gross_amount' => (string) $definition['amount'],
                'midtrans_payload' => ['channel' => $definition['payment_channel']],
                'payment_channel' => $definition['payment_channel'],
                'payment_reference' => $definition['payment_reference'],
                'payment_expired_at' => $definition['payment_expired_at'],
                'paid_at' => $definition['paid_at'] ?? null,
                'amount' => $definition['amount'],
                'status' => $definition['status'],
            ])->save();

            if ($definition['status'] === TransactionStatus::DIBAYAR->value) {
                PaymentNotification::updateOrCreate(
                    ['event_key' => $transaction->order_code . ':200:settlement'],
                    [
                        'transaction_id' => $transaction->id,
                        'source' => 'midtrans',
                        'order_id' => $transaction->order_code,
                        'transaction_status' => 'settlement',
                        'fraud_status' => 'accept',
                        'status_code' => '200',
                        'payload' => [
                            'order_id' => $transaction->order_code,
                            'transaction_status' => 'settlement',
                        ],
                        'is_processed' => true,
                        'processed_at' => Carbon::now(),
                    ]
                );
            }
        }
    }
}
