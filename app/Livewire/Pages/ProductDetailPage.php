<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\GroupMemberStatus;
use App\Enums\GroupStatus;
use App\Models\Group;
use App\Models\Product;
use App\Models\ProductItem;
use App\Services\OrderService;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductDetailPage extends Component
{
    public Product $product;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $packages = [];

    public ?int $selectedPackageId = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $groups = [];

    /**
     * @var array<int, array<string, string>>
     */
    public array $serviceHighlights = [];

    /**
     * @var array<int, string>
     */
    public array $infoBadges = [];

    public string $productImage;

    public function mount(Product $product): void
    {
        $product->load([
            'items' => fn ($query) => $query->where('is_active', true)->orderBy('price_per_user'),
            'categories:id,name',
        ]);

        $this->product = $product;
        $this->packages = $this->preparePackages();
        $this->selectedPackageId = $this->packages[0]['id'] ?? null;
        $this->groups = $this->prepareGroups();
        $this->serviceHighlights = $this->defaultHighlights();
        $this->infoBadges = $this->buildInfoBadges();
        $this->productImage = $this->resolveProductImage();
    }

    public function selectPackage(int $packageId): void
    {
        if (collect($this->packages)->contains(fn ($package) => $package['id'] === $packageId)) {
            $this->selectedPackageId = $packageId;
        }
    }

    public function orderNow(OrderService $orderService): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        if (! $this->selectedPackageId) {
            session()->flash('order_error', 'Pilih paket terlebih dahulu.');
            return;
        }

        $productItem = ProductItem::find($this->selectedPackageId);
        if (! $productItem) {
            session()->flash('order_error', 'Paket tidak ditemukan.');
            return;
        }

        try {
            $transaction = $orderService->createOrderForProductItem(auth()->user(), $productItem);
            $this->redirect(route('orders.show', $transaction->uuid), navigate: true);
        } catch (\RuntimeException $e) {
            session()->flash('order_error', $e->getMessage());
        }
    }

    public function joinGroup(int $groupId, OrderService $orderService): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $group = Group::find($groupId);
        if (! $group) {
            session()->flash('order_error', 'Grup tidak ditemukan.');
            return;
        }

        try {
            $transaction = $orderService->createOrderForGroup(auth()->user(), $group);
            $this->redirect(route('orders.show', $transaction->uuid), navigate: true);
        } catch (\RuntimeException $e) {
            session()->flash('order_error', $e->getMessage());
        }
    }

    public function render()
    {
        $selectedPackage = collect($this->packages)->firstWhere('id', $this->selectedPackageId);

        return view('livewire.pages.product-detail-page', [
            'selectedPackage' => $selectedPackage,
            'groupStats' => $this->groupStats(),
        ])->layout('layouts.marketing', [
            'title' => $this->product->name . ' · Patungin',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function preparePackages(): array
    {
        return $this->product->items
            ->map(function (ProductItem $item): array {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price_per_user,
                    'max_users' => $item->max_users,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function prepareGroups(): array
    {
        $itemIds = $this->product->items->pluck('id');

        if ($itemIds->isEmpty()) {
            return [];
        }

        $groups = Group::query()
            ->whereIn('product_item_id', $itemIds)
            ->with([
                'productItem:id,name,max_users,price_per_user,product_id',
                'productItem.product:id,duration',
                'members' => function ($query) {
                    $query->select('id', 'group_id', 'user_id', 'status', 'joined_at')
                        ->whereIn('status', [GroupMemberStatus::CONFIRMED, GroupMemberStatus::AKTIF])
                        ->with('user:id,name')
                        ->orderBy('joined_at');
                },
            ])
            ->withCount(['members as confirmed_members_count' => function ($query) {
                $query->whereIn('status', [GroupMemberStatus::CONFIRMED, GroupMemberStatus::AKTIF]);
            }])
            ->orderByDesc('updated_at')
            ->take(8)
            ->get()
            ->sortBy(fn (Group $group) => $this->statusWeight($group->status?->value))
            ->values();

        return $groups->map(function (Group $group): array {
            $maxUsers = (int) ($group->productItem?->max_users ?? 0);
            $filled = (int) ($group->confirmed_members_count ?? 0);
            $remaining = max($maxUsers - $filled, 0);
            $progress = $maxUsers > 0 ? min(100, (int) round(($filled / $maxUsers) * 100)) : 0;

            $memberNames = $group->members
                ->map(fn ($member) => $this->maskName($member->user?->name ?? 'Member'))
                ->values()
                ->all();

            $memberSlots = collect(range(1, max($maxUsers, 3)))
                ->map(fn ($slot) => $memberNames[$slot - 1] ?? 'Tersedia')
                ->take($maxUsers ?: 3)
                ->values()
                ->all();

            return [
                'id' => $group->id,
                'name' => $group->name,
                'status' => $group->status?->value ?? GroupStatus::AVAILABLE->value,
                'status_label' => $this->statusLabel($group->status?->value),
                'status_class' => $this->statusClass($group->status?->value),
                'price' => $group->productItem?->price_per_user,
                'max_users' => $maxUsers,
                'filled' => $filled,
                'remaining' => $remaining,
                'progress' => $progress,
                'members' => $memberSlots,
                'pre_order' => (bool) $group->pre_order,
                'tag' => $this->groupTag($remaining, $progress),
            ];
        })->all();
    }

    protected function statusWeight(?string $status): int
    {
        return match ($status) {
            GroupStatus::AVAILABLE->value => 0,
            GroupStatus::FULL->value => 1,
            GroupStatus::COMPLETED->value => 2,
            GroupStatus::CANCELLED->value => 3,
            default => 4,
        };
    }

    protected function maskName(string $name): string
    {
        $trimmed = trim($name);

        if ($trimmed === '') {
            return 'Member';
        }

        $chunks = preg_split('/\s+/', $trimmed) ?: [$trimmed];
        $chunks = array_slice($chunks, 0, 2);

        return collect($chunks)
            ->map(function (string $chunk): string {
                $length = mb_strlen($chunk);

                if ($length <= 2) {
                    return mb_substr($chunk, 0, 1) . str_repeat('*', max($length - 1, 1));
                }

                $first = mb_substr($chunk, 0, 1);
                $last = mb_substr($chunk, -1);

                return $first . str_repeat('*', $length - 2) . $last;
            })
            ->implode(' ');
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            GroupStatus::FULL->value => 'Penuh',
            GroupStatus::COMPLETED->value => 'Selesai',
            GroupStatus::CANCELLED->value => 'Dibatalkan',
            default => 'Aktif',
        };
    }

    protected function statusClass(?string $status): string
    {
        return match ($status) {
            GroupStatus::FULL->value => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
            GroupStatus::COMPLETED->value => 'bg-slate-200 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200',
            GroupStatus::CANCELLED->value => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
            default => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/30 dark:text-emerald-100',
        };
    }

    /**
     * @return array{label: string, class: string}
     */
    protected function groupTag(int $remaining, int $progress): array
    {
        if ($remaining === 0) {
            return ['label' => 'Penuh', 'class' => 'bg-slate-200 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200'];
        }

        if ($remaining === 1) {
            return ['label' => 'Hampir penuh', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200'];
        }

        if ($progress <= 25) {
            return ['label' => 'Baru buka', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'];
        }

        return ['label' => 'Slot tersedia', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200'];
    }

    protected function defaultHighlights(): array
    {
        return [
            [
                'title' => 'Midtrans + proteksi refund',
                'body' => 'Pembayaran otomatis diverifikasi dan dana aman bila grup batal.',
            ],
            [
                'title' => 'Admin pendamping 7x24 jam',
                'body' => 'Tim kami bantu aktivasi, reset akun, hingga reminder jatuh tempo.',
            ],
            [
                'title' => 'Monitoring slot realtime',
                'body' => 'Lihat progres grup, status pre-order, dan histori anggota kapan saja.',
            ],
        ];
    }

    protected function buildInfoBadges(): array
    {
        $categoryBadges = $this->product->categories
            ->pluck('name')
            ->take(2)
            ->map(fn ($name) => $name . ' pilihan komunitas')
            ->all();

        $defaults = [
            'Proteksi Midtrans',
            'Garansi uang kembali',
            'Admin respons cepat',
        ];

        return array_values(array_unique(array_merge($categoryBadges, $defaults)));
    }

    protected function resolveProductImage(): string
    {
        $image = $this->product->image;

        if ($image) {
            if (Str::startsWith($image, ['http://', 'https://'])) {
                return $image;
            }

            $path = Str::startsWith($image, ['storage/', 'images/'])
                ? $image
                : 'storage/' . ltrim($image, '/');

            return asset($path);
        }

        $initials = urlencode(Str::upper(Str::substr($this->product->name ?? 'PA', 0, 2)));

        return "https://placehold.co/128x128/0f172a/ffffff?text={$initials}";
    }

    /**
     * @return array<string, int>
     */
    protected function groupStats(): array
    {
        $groups = collect($this->groups);

        return [
            'total' => $groups->count(),
            'active' => $groups->where('status', GroupStatus::AVAILABLE->value)->count(),
            'full' => $groups->where('status', GroupStatus::FULL->value)->count(),
        ];
    }
}
