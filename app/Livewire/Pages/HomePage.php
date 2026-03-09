<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\GroupStatus;
use App\Models\Group;
use App\Models\Product;
use Livewire\Component;

class HomePage extends Component
{
    /**
     * @var array<int, string>
     */
    public array $featureTags = [];

    /**
     * @var array<int, array<string, string|bool>>
     */
    public array $ctaButtons = [];

    /**
     * @var array<int, array<string, string>>
     */
    public array $heroPerks = [];

    public function mount(): void
    {
        $this->featureTags = [
            'Hemat sampai 80% dari harga normal',
            'Pembayaran aman & otomatis',
            'Komunitas patungan terkurasi',
        ];

        $this->ctaButtons = [
            ['label' => 'Mulai Patungan Sekarang', 'href' => '#layanan', 'primary' => true],
            ['label' => 'Cek Patungan Terakhir Saya', 'href' => '#riwayat', 'primary' => false],
        ];

        $this->heroPerks = [
            ['title' => 'Full garansi', 'body' => 'Uang kembali seketika bila kuota tidak terpenuhi.'],
            ['title' => 'Live monitoring', 'body' => 'Notifikasi status grup, pembayaran, & seat langsung di dashboard.'],
            ['title' => 'Support 7x24 jam', 'body' => 'Tim admin sigap bantu aktivasi & kendala akun kamu.'],
        ];
    }

    public function render()
    {
        return view('livewire.pages.home-page', [
            'stats' => $this->buildStats(),
        ])->layout('layouts.marketing', [
            'title' => 'Patungan Layanan Premium',
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function buildStats(): array
    {
        $activeProducts = Product::query()->where('is_active', true)->count();
        $activeGroups = Group::query()
            ->whereIn('status', [GroupStatus::AVAILABLE, GroupStatus::FULL])
            ->count();
        $completedGroups = Group::query()->where('status', GroupStatus::COMPLETED)->count();

        return [
            ['label' => 'Layanan Premium', 'value' => $this->formatStat($activeProducts)],
            ['label' => 'Grup Aktif', 'value' => $this->formatStat($activeGroups)],
            ['label' => 'Grup Selesai', 'value' => $this->formatStat($completedGroups)],
        ];
    }

    protected function formatStat(int $value): string
    {
        if ($value >= 10_000) {
            return number_format($value / 1_000, 0) . 'K+';
        }

        if ($value >= 1_000) {
            return number_format($value / 1_000, 1) . 'K+';
        }

        return number_format($value);
    }
}
