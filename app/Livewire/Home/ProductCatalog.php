<?php

declare(strict_types=1);

namespace App\Livewire\Home;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductCatalog extends Component
{
    public int $perPage = 12;

    public bool $ready = false;

    public int $totalProducts = 0;

    /**
     * @var array<int, string>
     */
    protected array $accentPalette = [
        'from-orange-500 to-rose-500',
        'from-blue-500 to-cyan-500',
        'from-emerald-500 to-lime-500',
        'from-fuchsia-500 to-purple-500',
        'from-amber-500 to-orange-600',
        'from-indigo-500 to-sky-500',
    ];

    public function mount(): void
    {
        $this->totalProducts = Product::query()->where('is_active', true)->count();
    }

    public function loadProducts(): void
    {
        $this->ready = true;
    }

    public function loadMore(): void
    {
        $next = $this->perPage + 6;

        if ($this->totalProducts > 0) {
            $this->perPage = min($next, $this->totalProducts);

            return;
        }

        $this->perPage = $next;
    }

    public function render()
    {
        $products = $this->ready ? $this->queryProducts() : collect();

        return view('livewire.home.product-catalog', [
            'products' => $products,
            'skeleton' => ! $this->ready,
            'hasMore' => $this->ready && $this->totalProducts > $this->perPage,
            'visibleCount' => $products->count(),
            'totalProducts' => $this->totalProducts,
        ]);
    }

    protected function queryProducts(): Collection
    {
        return Product::query()
            ->with(['items' => function ($query) {
                $query->where('is_active', true)->orderBy('price_per_user');
            }])
            ->where('is_active', true)
            ->orderBy('name')
            ->take($this->perPage)
            ->get()
            ->map(function (Product $product, int $index): array {
                $primaryPlan = $product->items->first();
                $description = $product->description ?: 'Nikmati akses premium bersama harga patungan. Tibalah notifikasi setiap update status grup.';

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'duration' => $product->duration ?? 30,
                    'description' => Str::of($description)->limit(96)->toString(),
                    'price' => $primaryPlan?->price_per_user,
                    'max_users' => $primaryPlan?->max_users,
                    'accent' => $this->accentForIndex($index),
                    'image' => $this->imageUrl($product),
                    'image_alt' => $product->name . ' logo',
                ];
            });
    }

    protected function accentForIndex(int $index): string
    {
        $paletteIndex = $index % count($this->accentPalette);

        return $this->accentPalette[$paletteIndex];
    }

    protected function imageUrl(Product $product): string
    {
        if ($product->image) {
            if (Str::startsWith($product->image, ['http://', 'https://'])) {
                return $product->image;
            }

            $path = Str::startsWith($product->image, ['storage/', 'images/'])
                ? $product->image
                : 'storage/' . ltrim($product->image, '/');

            return asset($path);
        }

        $initials = urlencode(Str::upper(Str::substr($product->name, 0, 2)));

        return "https://placehold.co/96x96/0f172a/ffffff?text={$initials}";
    }
}
