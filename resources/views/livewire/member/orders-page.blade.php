<div class="relative isolate overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-[-180px] z-0 mx-auto h-[380px] w-[640px] rounded-full blur-[120px]" style="background: radial-gradient(circle at 20% 20%, rgba(255,180,0,0.35), transparent), radial-gradient(circle at 80% 0%, rgba(255,64,129,0.25), transparent);"></div>

    <x-layouts.marketing-header :prefix="route('home')" :authUser="auth()->user()" />

    <main class="relative z-10 pt-28 pb-24">
        <div class="mx-auto max-w-4xl space-y-8 px-6">

            <div>
                <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-slate-500 transition hover:text-orange-500 dark:text-slate-400 dark:hover:text-orange-300">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M15 18l-6-6 6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Dashboard
                </a>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">Pesanan Saya</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Riwayat semua pesanan dan transaksi Anda.</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Pencarian</label>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari order..." class="mt-1 w-64 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Status</label>
                        <select wire:model.live="status" class="mt-1 w-44 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option value="">Semua</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st->value }}">{{ $st->getLabel() ?? ucfirst($st->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Dari Tanggal</label>
                        <input type="date" wire:model.live="dateFrom" class="mt-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-300">Sampai</label>
                        <input type="date" wire:model.live="dateTo" class="mt-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                </div>
            </div>

            @if($orders->isEmpty())
                <div class="flex flex-col items-center justify-center gap-5 rounded-[28px] border border-slate-200 bg-white py-20 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        <svg class="h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada pesanan</p>
                        <p class="mx-auto mt-2 max-w-sm text-xs leading-relaxed text-slate-500 dark:text-slate-400">Pesanan yang Anda buat akan tampil di sini.</p>
                    </div>
                    <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/30 transition hover:-translate-y-0.5">
                        Jelajahi Produk
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($orders as $order)
                        @php
                            $product = $order->groupMember?->group?->productItem?->product;
                        @endphp
                        <a href="{{ route('orders.show', ['transaction' => $order->uuid]) }}" wire:navigate
                           class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-emerald-500/40">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                                @if($product?->image)
                                    <img src="{{ asset('storage/' . ltrim($product->image, '/')) }}" alt="{{ $product->name }}" class="h-12 w-12 rounded-xl object-cover">
                                @else
                                    <svg class="h-6 w-6 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $product?->name ?? 'Pesanan' }}</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $order->order_code }} · {{ $order->created_at?->format('d M Y') }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ match($order->status?->value) {
                                    'DIBAYAR' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                                    'MENUNGGU' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                                    'DIBATALKAN' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'
                                } }}">
                                    {{ $order->status?->getLabel() ?? ucfirst($order->status?->value) }}
                                </span>
                                <span class="whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-white">Rp {{ number_format((int) $order->amount, 0, ',', '.') }}</span>
                            </div>
                            <svg class="ml-1 h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-emerald-400 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M9 18l6-6-6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @endforeach
                </div>

                @if($hasMore)
                    <div class="mt-6 flex justify-center">
                        <button wire:click="loadMore" class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/30 transition hover:-translate-y-0.5">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 6v12m6-6H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Muat Lebih Banyak
                        </button>
                    </div>
                @endif
            @endif

        </div>
    </main>
</div>
