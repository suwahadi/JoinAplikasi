@php
    use App\Enums\TransactionStatus;

    $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $fmtDate = fn(\Carbon\Carbon $d) => $d->day . ' ' . $months[$d->month - 1] . ' ' . $d->year;

    $statusMap = [
        TransactionStatus::MENUNGGU_PEMBAYARAN->value => ['label' => 'Menunggu Bayar',  'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300'],
        TransactionStatus::DIBAYAR->value             => ['label' => 'Lunas',           'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300'],
        TransactionStatus::GAGAL->value               => ['label' => 'Gagal',           'class' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300'],
        TransactionStatus::KEDALUWARSA->value         => ['label' => 'Kedaluwarsa',     'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'],
        TransactionStatus::DIBATALKAN->value          => ['label' => 'Dibatalkan',      'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'],
        TransactionStatus::DIREFUND->value            => ['label' => 'Direfund',        'class' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300'],
    ];

    $user = auth()->user();
    $firstName = explode(' ', trim($user->name))[0];
@endphp

<div class="relative isolate overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-[-180px] z-0 mx-auto h-[380px] w-[640px] rounded-full blur-[120px]" style="background: radial-gradient(circle at 20% 20%, rgba(255,180,0,0.35), transparent), radial-gradient(circle at 80% 0%, rgba(255,64,129,0.25), transparent);"></div>

    <x-layouts.marketing-header :prefix="route('home')" :authUser="auth()->user()" />

    <main class="relative z-10 pt-28 pb-24">
        <div class="mx-auto max-w-6xl space-y-8 px-6">

            {{-- Page Header --}}
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-500">Akun Saya</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">Halo, {{ $firstName }}</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pantau semua langganan patungan kamu di sini.</p>
                </div>
                <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/30 transition hover:-translate-y-0.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 5v14m-7-7h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Langganan Baru
                </a>
            </div>

            {{-- Stats Grid --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Total Pesanan</p>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                            <svg class="h-4 w-4 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ $totalCount }}</p>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Semua transaksi</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Berhasil Lunas</p>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10">
                            <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-semibold text-slate-900 dark:text-white">{{ $paidCount }}</p>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Pembayaran dikonfirmasi</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Total Dikeluarkan</p>
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-orange-50 dark:bg-orange-500/10">
                            <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Dari transaksi lunas</p>
                </div>
            </div>

            {{-- Recent Transactions --}}
            <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Riwayat Pesanan</h2>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">8 pesanan terakhir</p>
                    </div>
                    <a href="/orders" wire:navigate class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                        Lihat Semua
                    </a>
                </div>

                @if($transactions->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-4 py-20 text-center">
                        <span class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                            <svg class="h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Belum ada pesanan</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mulai patungan sekarang dan hemat biaya langganan.</p>
                        </div>
                        <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/30 transition hover:-translate-y-0.5">
                            Jelajahi Produk
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($transactions as $tx)
                            @php
                                $gm      = $tx->groupMember;
                                $product = $gm?->group?->productItem?->product;
                                $si      = $statusMap[$tx->status->value] ?? ['label' => $tx->status->value, 'class' => 'bg-slate-100 text-slate-600'];
                            @endphp
                            <a href="{{ route('orders.show', $tx->uuid) }}" wire:navigate class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                                    @if($product?->image)
                                        <img src="{{ asset('storage/' . ltrim($product->image, '/')) }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-xl object-cover">
                                    @else
                                        <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <rect x="2" y="3" width="20" height="14" rx="2" stroke-width="1.5"/>
                                            <path d="M8 21h8m-4-4v4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $product?->name ?? 'Produk' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $tx->order_code }} &middot; {{ $fmtDate($tx->created_at) }}</p>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-1.5">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $si['class'] }}">
                                        {{ $si['label'] }}
                                    </span>
                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300">Rp {{ number_format($tx->amount, 0, ',', '.') }}</p>
                                </div>
                                <svg class="ml-1 h-4 w-4 shrink-0 text-slate-300 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M9 18l6-6-6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Quick Actions --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <a href="{{ route('member.deliveries') }}" wire:navigate class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-emerald-500/40">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 transition group-hover:bg-emerald-100 dark:bg-emerald-500/10 dark:group-hover:bg-emerald-500/20">
                        <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Delivery Saya</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Akses kredensial layanan premium</p>
                    </div>
                    <svg class="ml-auto h-4 w-4 text-slate-300 transition group-hover:text-emerald-400 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 18l6-6-6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                <a href="{{ route('home') }}" wire:navigate class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-orange-500/40">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 transition group-hover:bg-orange-100 dark:bg-orange-500/10 dark:group-hover:bg-orange-500/20">
                        <svg class="h-5 w-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 22V12h6v10" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Jelajahi Produk</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Temukan layanan untuk dipatungin</p>
                    </div>
                    <svg class="ml-auto h-4 w-4 text-slate-300 transition group-hover:text-orange-400 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 18l6-6-6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                <a href="{{ route('profile') }}" wire:navigate class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-orange-500/40">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 transition group-hover:bg-slate-200 dark:bg-slate-800 dark:group-hover:bg-slate-700">
                        <svg class="h-5 w-5 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="8" r="4" stroke-width="1.5"/>
                            <path d="M4 20c0-3.866 3.582-7 8-7s8 3.134 8 7" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Edit Profil</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Ubah nama, email, dan password</p>
                    </div>
                    <svg class="ml-auto h-4 w-4 text-slate-300 transition group-hover:text-orange-400 dark:text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M9 18l6-6-6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

        </div>
    </main>
</div>
