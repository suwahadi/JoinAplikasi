@php
    $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $fmtDate = fn(\Carbon\Carbon $d) => $d->day . ' ' . $months[$d->month - 1] . ' ' . $d->year;
@endphp

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
                <h1 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">Delivery Saya</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Akses kredensial layanan premium yang sudah kamu bayar.</p>
            </div>

            @if ($items->isEmpty())
                <div class="flex flex-col items-center justify-center gap-5 rounded-[28px] border border-slate-200 bg-white py-20 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        <svg class="h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada delivery aktif</p>
                        <p class="mx-auto mt-2 max-w-sm text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            Delivery akan tersedia setelah semua seat dalam grup kamu terisi dan semua anggota sudah menyelesaikan pembayaran.
                        </p>
                    </div>
                    <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/30 transition hover:-translate-y-0.5">
                        Jelajahi Produk
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($items as $item)
                        @php
                            $product = $item->delivery?->group?->productItem?->product;
                            $productItem = $item->delivery?->group?->productItem;
                            $group = $item->delivery?->group;
                            $delivery = $item->delivery;
                            $expiresAt = $delivery?->expires_at;
                            $daysLeft = $expiresAt ? (int) now()->diffInDays($expiresAt, false) : null;
                        @endphp
                        <a href="{{ route('member.deliveries.show', $item) }}" wire:navigate
                           class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-emerald-500/40">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                                @if($product?->image)
                                    <img src="{{ asset('storage/' . ltrim($product->image, '/')) }}" alt="{{ $product->name }}" class="h-12 w-12 rounded-xl object-cover">
                                @else
                                    <svg class="h-6 w-6 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $product?->name ?? 'Produk' }}</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $group?->name ?? '-' }} · {{ $productItem?->name ?? '-' }}</p>
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-1.5">
                                @if($daysLeft !== null && $daysLeft < 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Kedaluwarsa
                                    </span>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $fmtDate($expiresAt) }}</p>
                                @elseif($daysLeft !== null && $daysLeft <= 7)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Segera Habis
                                    </span>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $daysLeft }} hari tersisa</p>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                    @if($expiresAt)
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $daysLeft }} hari tersisa</p>
                                    @else
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Tanpa batas waktu</p>
                                    @endif
                                @endif
                            </div>
                            <svg class="ml-1 h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-emerald-400 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M9 18l6-6-6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $items->links() }}
                </div>
            @endif

        </div>
    </main>
</div>
