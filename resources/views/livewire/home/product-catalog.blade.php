<section id="layanan" class="mt-24" wire:init="loadProducts">
    <x-home.section-heading
        eyebrow="Produk Patungan Premium"
        title="Pilih layanan favorit kamu"
        subtitle="Semua harga sudah termasuk patungan aman + pengingat otomatis setiap siklus."
    />

    <div class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @if($skeleton)
            @foreach(range(1, 6) as $index)
                <div class="rounded-3xl border border-slate-200 bg-white/60 p-6 shadow animate-pulse dark:border-slate-800 dark:bg-slate-900/50">
                    <div class="flex items-start gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-slate-200 dark:bg-slate-800"></div>
                        <div class="flex-1 space-y-3">
                            <div class="h-4 w-2/3 rounded bg-slate-200 dark:bg-slate-800"></div>
                            <div class="h-3 w-full rounded bg-slate-200 dark:bg-slate-800"></div>
                        </div>
                    </div>
                    <div class="mt-6 space-y-3">
                        <div class="h-4 w-1/3 rounded bg-slate-200 dark:bg-slate-800"></div>
                        <div class="h-6 w-1/2 rounded bg-slate-200 dark:bg-slate-800"></div>
                        <div class="h-3 w-1/3 rounded bg-slate-200 dark:bg-slate-800"></div>
                    </div>
                    <div class="mt-6 h-9 rounded-2xl bg-slate-200 dark:bg-slate-800"></div>
                </div>
            @endforeach
        @else
            @forelse($products as $product)
                <div wire:key="product-{{ $product['id'] }}" class="h-full">
                    <x-home.product-card
                        :title="$product['name']"
                        :description="$product['description']"
                        :price="$product['price']"
                        :duration="$product['duration']"
                        :max-users="$product['max_users']"
                        :accent="$product['accent']"
                        :image="$product['image']"
                        :image-alt="$product['image_alt']"
                        :badge="$product['price'] ? null : 'Segera hadir'"
                        href="{{ route('products.show', $product['slug']) }}"
                        class="h-full transition duration-200 hover:-translate-y-1"
                    />
                </div>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-200 bg-white/40 p-10 text-center text-slate-500 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-300">
                    Layanan premium baru akan segera tersedia. Cek lagi nanti, ya!
                </div>
            @endforelse
        @endif
    </div>

    @if($hasMore)
        <div class="mt-12 text-center">
            <button
                type="button"
                wire:click="loadMore"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-slate-900/5 transition hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            >
                <span wire:loading.remove>Load 6 layanan lagi</span>
                <span wire:loading.flex class="items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Memuat...
                </span>
            </button>
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                Menampilkan {{ $visibleCount }} dari {{ $totalProducts }} layanan aktif.
            </p>
        </div>
    @endif
</section>
