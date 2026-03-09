<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Enums\GroupMemberStatus;
use App\Enums\GroupStatus;
use App\Models\Group;
use App\Models\ProductItem;
use Illuminate\Support\Collection;
use Livewire\Component;

class GroupShowcase extends Component
{
    /**
     * @var array<int, array<string, int|string>>
     */
    public array $services = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $groups = [];

    public ?int $selectedServiceId = null;

    public bool $ready = false;

    public function mount(): void
    {
        $this->services = $this->serviceTabs()->map(function (ProductItem $item): array {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'product' => $item->product?->name ?? $item->name,
                'group_count' => (int) $item->groups_count,
            ];
        })->all();

        $this->selectedServiceId = $this->services[0]['id'] ?? null;
    }

    public function load(): void
    {
        $this->ready = true;

        if ($this->selectedServiceId) {
            $this->loadGroupsForService($this->selectedServiceId);
        }
    }

    public function selectService(int $serviceId): void
    {
        $this->selectedServiceId = $serviceId;

        if ($this->ready) {
            $this->loadGroupsForService($serviceId);
        }
    }

    public function render()
    {
        return view('livewire.home.group-showcase');
    }

    protected function loadGroupsForService(int $serviceId): void
    {
        $groups = Group::query()
            ->where('product_item_id', $serviceId)
            ->with([
                'productItem:id,name,max_users,product_id',
                'productItem.product:id,name,duration',
                'members' => function ($query) {
                    $query->select('id', 'group_id', 'user_id', 'status', 'joined_at')
                        ->with('user:id,name')
                        ->whereIn('status', [GroupMemberStatus::CONFIRMED, GroupMemberStatus::AKTIF])
                        ->orderBy('joined_at');
                },
            ])
            ->withCount(['members as confirmed_members_count' => function ($query) {
                $query->whereIn('status', [GroupMemberStatus::CONFIRMED, GroupMemberStatus::AKTIF]);
            }])
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        $this->groups = $groups->map(function (Group $group): array {
            $maxUsers = (int) ($group->productItem?->max_users ?? 0);
            $filled = (int) ($group->confirmed_members_count ?? 0);
            $percentage = $maxUsers > 0 ? min(100, (int) round(($filled / $maxUsers) * 100)) : 0;

            $members = $group->members->take(6)->map(function ($member) {
                $name = $member->user?->name ?? 'Member';

                return $this->maskName($name);
            })->all();

            return [
                'id' => $group->id,
                'name' => $group->name,
                'status' => $group->status?->value ?? GroupStatus::AVAILABLE->value,
                'status_label' => $this->statusLabel($group->status?->value),
                'status_class' => $this->statusClass($group->status?->value),
                'service' => $group->productItem?->product?->name ?? $group->productItem?->name,
                'price' => $group->productItem?->price_per_user,
                'duration' => $group->productItem?->product?->duration ?? 30,
                'max_users' => $maxUsers,
                'filled' => $filled,
                'percentage' => $percentage,
                'members' => $members,
            ];
        })->all();
    }

    protected function serviceTabs(): Collection
    {
        return ProductItem::query()
            ->select(['id', 'name', 'product_id'])
            ->with(['product:id,name'])
            ->withCount(['groups as groups_count' => function ($query) {
                $query->whereIn('status', [GroupStatus::AVAILABLE, GroupStatus::FULL]);
            }])
            ->orderByDesc('groups_count')
            ->take(12)
            ->get();
    }

    protected function maskName(string $name): string
    {
        if ($name === '') {
            return 'Anon';
        }

        $chunks = preg_split('/\s+/', trim($name)) ?: ['Member'];
        $masked = array_map(function (string $chunk) {
            $first = mb_substr($chunk, 0, 1);
            $last = mb_substr($chunk, -1);

            return $first . str_repeat('*', max(mb_strlen($chunk) - 2, 1)) . $last;
        }, array_slice($chunks, 0, 2));

        return implode(' ', $masked);
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
            default => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
        };
    }
}
