@php
    $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $fmtDate = fn(\Carbon\Carbon $d) => $d->day . ' ' . $months[$d->month - 1] . ' ' . $d->year;

    $credential = $deliveryItem->credential;
    $delivery = $deliveryItem->delivery;
    $group = $delivery?->group;
    $product = $group?->productItem?->product;
    $productItem = $group?->productItem;
    $expiresAt = $delivery?->expires_at;
    $daysLeft = $expiresAt ? (int) now()->diffInDays($expiresAt, false) : null;
    $isExpired = $delivery?->status === \App\Enums\DeliveryStatus::EXPIRED || ($daysLeft !== null && $daysLeft < 0);
    $pwd = $credential?->password ?? '';
@endphp

<div class="relative isolate overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-[-180px] z-0 mx-auto h-[380px] w-[640px] rounded-full blur-[120px]" style="background: radial-gradient(circle at 20% 20%, rgba(255,180,0,0.35), transparent), radial-gradient(circle at 80% 0%, rgba(255,64,129,0.25), transparent);"></div>

    <x-layouts.marketing-header :prefix="route('home')" :authUser="auth()->user()" />

    <main class="relative z-10 pt-28 pb-24">
        <div class="mx-auto max-w-3xl space-y-6 px-6">

            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <a href="{{ route('dashboard') }}" wire:navigate class="transition hover:text-orange-500 dark:hover:text-orange-300">Dashboard</a>
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 18l6-6-6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <a href="{{ route('member.deliveries') }}" wire:navigate class="transition hover:text-orange-500 dark:hover:text-orange-300">Delivery Saya</a>
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 18l6-6-6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-700 dark:text-slate-200">Detail</span>
            </div>

            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                        @if($product?->image)
                            <img src="{{ asset('storage/' . ltrim($product->image, '/')) }}" alt="{{ $product->name }}" class="h-14 w-14 rounded-2xl object-cover">
                        @else
                            <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $product?->name ?? 'Produk' }}</h1>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $group?->name ?? '-' }} · {{ $productItem?->name ?? '-' }}</p>
                    </div>
                </div>
                @if($isExpired)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        Kedaluwarsa
                    </span>
                @elseif($daysLeft !== null && $daysLeft <= 7)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Segera Habis
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                @endif
            </div>

            @if($expiresAt)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Masa Aktif</p>
                            <p class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                                {{ $fmtDate($delivery->activated_at) }} &mdash; {{ $fmtDate($expiresAt) }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($daysLeft !== null && $daysLeft >= 0)
                                <p class="text-2xl font-semibold {{ $daysLeft <= 7 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $daysLeft }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">hari tersisa</p>
                            @else
                                <p class="text-sm font-medium text-red-500">Kedaluwarsa</p>
                            @endif
                        </div>
                    </div>
                    @if($daysLeft !== null && $daysLeft >= 0)
                        @php
                            $totalDays = (int) \Carbon\Carbon::parse($delivery->activated_at)->diffInDays($expiresAt);
                            $progress = $totalDays > 0 ? max(0, min(100, (int) round((($totalDays - $daysLeft) / $totalDays) * 100))) : 100;
                        @endphp
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full {{ $daysLeft <= 7 ? 'bg-gradient-to-r from-red-400 to-red-500' : 'bg-gradient-to-r from-emerald-400 to-emerald-500' }} transition-all" style="width: {{ $progress }}%"></div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="{ showPwd: false, copiedUser: false, copiedPwd: false }">
                <div class="border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">Kredensial Akses</h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Gunakan informasi ini untuk login ke layanan.</p>
                </div>

                <div class="space-y-5 px-6 py-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Username / Email</p>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                {{ $credential?->username ?? '-' }}
                            </div>
                            <button
                                type="button"
                                x-on:click="navigator.clipboard.writeText(@js($credential?->username ?? '')); copiedUser = true; setTimeout(() => copiedUser = false, 2000)"
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                title="Salin username"
                            >
                                <svg x-show="!copiedUser" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="9" y="9" width="13" height="13" rx="2" stroke-width="1.5"/>
                                    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" stroke-width="1.5"/>
                                </svg>
                                <svg x-show="copiedUser" x-cloak class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Kata Sandi</p>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <span x-show="!showPwd">{{ str_repeat('•', max(8, strlen($pwd))) }}</span>
                                <span x-show="showPwd" x-cloak>{{ $pwd }}</span>
                            </div>
                            <button
                                type="button"
                                x-on:click="showPwd = !showPwd"
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                title="Tampilkan/Sembunyikan"
                            >
                                <svg x-show="!showPwd" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke-width="1.5"/>
                                    <circle cx="12" cy="12" r="3" stroke-width="1.5"/>
                                </svg>
                                <svg x-show="showPwd" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="1" y1="1" x2="23" y2="23" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <button
                                type="button"
                                x-on:click="navigator.clipboard.writeText(@js($pwd)); copiedPwd = true; setTimeout(() => copiedPwd = false, 2000)"
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                title="Salin password"
                            >
                                <svg x-show="!copiedPwd" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="9" y="9" width="13" height="13" rx="2" stroke-width="1.5"/>
                                    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" stroke-width="1.5"/>
                                </svg>
                                <svg x-show="copiedPwd" x-cloak class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if ($credential?->instructions_markdown)
                <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Instruksi Aktivasi</h2>
                    </div>
                    <div class="prose prose-sm max-w-none px-6 py-5 text-slate-700 dark:prose-invert dark:text-slate-300">
                        {!! \Illuminate\Support\Str::markdown($credential->instructions_markdown) !!}
                    </div>
                </div>
            @endif

            <div class="flex justify-center pt-4">
                <a href="{{ route('member.deliveries') }}" wire:navigate class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-medium text-slate-600 transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M15 18l-6-6 6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kembali ke Daftar Delivery
                </a>
            </div>

        </div>
    </main>
</div>
