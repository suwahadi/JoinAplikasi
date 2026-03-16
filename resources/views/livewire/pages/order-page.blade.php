@php
    use App\Enums\TransactionStatus;
    use App\Enums\PaymentChannel;
    use Illuminate\Support\Str;

    $groupMember  = $transaction->groupMember;
    $group        = $groupMember->group;
    $productItem  = $group->productItem;
    $product      = $productItem->product;
    $user         = $groupMember->user;

    $productImage = (function() use ($product): string {
        $image = $product->image ?? null;
        if ($image) {
            if (Str::startsWith($image, ['http://', 'https://'])) {
                return $image;
            }
            $path = Str::startsWith($image, ['storage/', 'images/']) ? $image : 'storage/' . ltrim($image, '/');
            return asset($path);
        }
        $initials = urlencode(Str::upper(Str::substr($product->name ?? 'PA', 0, 2)));
        return "https://placehold.co/80x80/0f172a/ffffff?text={$initials}";
    })();

    $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $fmtDate = fn(\Carbon\Carbon $d) =>
        $d->day . ' ' . $months[$d->month - 1] . ' ' . $d->year . ', ' . $d->format('H:i');

    $createdAt = $fmtDate($transaction->created_at);
    $expiredAt = $transaction->payment_expired_at ? $fmtDate($transaction->payment_expired_at) : '-';

    $statusMap = [
        TransactionStatus::MENUNGGU_PEMBAYARAN->value => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200'],
        TransactionStatus::DIBAYAR->value             => ['label' => 'Lunas', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'],
        TransactionStatus::GAGAL->value               => ['label' => 'Gagal', 'class' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-200'],
        TransactionStatus::KEDALUWARSA->value         => ['label' => 'Kedaluwarsa', 'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'],
        TransactionStatus::DIBATALKAN->value          => ['label' => 'Dibatalkan', 'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'],
        TransactionStatus::DIREFUND->value            => ['label' => 'Direfund', 'class' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200'],
    ];
    $statusInfo = $statusMap[$transaction->status->value] ?? ['label' => $transaction->status->value, 'class' => 'bg-slate-100 text-slate-600'];

    // Cari DeliveryItem untuk member ini bila sudah tersedia agar dapat menampilkan tautan langsung
    $deliveryItem = \App\Models\DeliveryItem::query()
        ->where('group_member_id', $groupMember->id)
        ->whereHas('delivery', fn($q) => $q->whereIn('status', [\App\Enums\DeliveryStatus::ACTIVE, \App\Enums\DeliveryStatus::EXPIRED]))
        ->latest('id')
        ->first();
@endphp

<div class="relative isolate overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-[-200px] z-0 mx-auto h-[420px] w-[720px] rounded-full blur-[140px]" style="background: radial-gradient(circle at 20% 20%, rgba(255,180,0,0.45), transparent), radial-gradient(circle at 80% 0%, rgba(255,64,129,0.3), transparent);"></div>

    <x-layouts.marketing-header :prefix="route('home')" />

    <main class="relative z-10 pt-32 pb-24">
        <div class="mx-auto max-w-6xl space-y-8 px-6">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
                <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 shadow-sm transition hover:-translate-y-0.5 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M15 19l-7-7 7-7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Beranda
                </a>
                <span>/</span>
                <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="text-slate-700 hover:underline dark:text-slate-200">{{ $product->name }}</a>
                <span>/</span>
                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $transaction->order_code }}</span>
            </div>

            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-500">
                        @if($transaction->status === TransactionStatus::MENUNGGU_PEMBAYARAN)
                            Konfirmasi Pesanan
                        @elseif($transaction->status === TransactionStatus::DIBAYAR && isset($deliveryItem) && $deliveryItem)
                            Delivery Aktif
                        @elseif($transaction->status === TransactionStatus::DIBAYAR)
                            Pembayaran Berhasil
                        @else
                            Status Pesanan
                        @endif
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">
                        @if($transaction->status === TransactionStatus::MENUNGGU_PEMBAYARAN)
                            Selesaikan Pembayaran
                        @elseif($transaction->status === TransactionStatus::DIBAYAR && isset($deliveryItem) && $deliveryItem)
                            Akses Layanan Siap
                        @elseif($transaction->status === TransactionStatus::DIBAYAR)
                            Pembayaran Dikonfirmasi
                        @else
                            Ringkasan Pesanan
                        @endif
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Order dibuat {{ $createdAt }}</p>
                </div>
                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold {{ $statusInfo['class'] }}">
                    @if($transaction->status === TransactionStatus::MENUNGGU_PEMBAYARAN)
                        <svg class="mr-2 h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
                            <path d="M12 6v6l4 2" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    @elseif($transaction->status === TransactionStatus::DIBAYAR)
                        <svg class="mr-2 h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @endif
                    {{ $statusInfo['label'] }}
                </span>
            </div>

            {{-- Error message --}}
            @if($errorMessage)
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
                            <path d="M12 8v4M12 16h.01" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <p>{{ $errorMessage }}</p>
                    </div>
                </div>
            @endif

            {{-- Main Grid --}}
            <div class="grid gap-6 lg:grid-cols-[1fr_380px]">

                {{-- LEFT: Payment Section --}}
                <div
                    class="space-y-5 relative"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="selectGateway,loadDuitkuMethods"
                    aria-busy="true"
                >

                    {{-- Section-level loading overlay saat memuat Duitku --}}
                    <div
                        wire:loading.delay.short
                        wire:target="selectGateway,loadDuitkuMethods"
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center"
                    >
                        <div class="rounded-2xl border border-slate-200 bg-white/80 px-5 py-4 shadow-lg backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
                            <div class="flex items-center gap-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"/>
                                </svg>
                                <span>Memuat metode pembayaran...</span>
                            </div>
                        </div>
                    </div>

                    @if($pageState === 'awaiting_selection')
                    {{-- Prefetch Duitku methods di background agar cepat saat tab diklik --}}
                    @if($selectedGateway !== 'duitku')
                        <div wire:init="loadDuitkuMethods" class="hidden"></div>
                    @endif

                    {{-- Gateway Selection --}}
                    <div class="rounded-[28px] border border-slate-200 bg-white/95 p-7 shadow-[0_35px_80px_rgba(15,23,42,0.12)] dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-500">Langkah 1</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">Pilih Payment Gateway</h2>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <button type="button"
                                wire:click="selectGateway('midtrans')"
                                wire:loading.attr="disabled"
                                wire:target="selectGateway,loadDuitkuMethods"
                                class="inline-flex items-center gap-2 rounded-2xl border px-4 py-3 text-sm font-semibold transition {{ $selectedGateway === 'midtrans' ? 'border-slate-900 bg-slate-900 text-white dark:border-white dark:bg-white dark:text-slate-900' : 'border-slate-200 bg-white/70 text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-200' }}">
                                Midtrans
                            </button>
                            <button type="button"
                                wire:click="selectGateway('duitku')"
                                wire:loading.attr="disabled"
                                wire:target="selectGateway,loadDuitkuMethods"
                                class="inline-flex items-center gap-2 rounded-2xl border px-4 py-3 text-sm font-semibold transition {{ $selectedGateway === 'duitku' ? 'border-slate-900 bg-slate-900 text-white dark:border-white dark:bg-white dark:text-slate-900' : 'border-slate-200 bg-white/70 text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-200' }}">
                                <span>Duitku</span>
                                <span class="ml-1 inline-flex" wire:loading wire:target="selectGateway,loadDuitkuMethods">
                                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-current [animation-delay:-0.2s]"></span>
                                    <span class="mx-0.5 h-1.5 w-1.5 animate-bounce rounded-full bg-current"></span>
                                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-current [animation-delay:0.2s]"></span>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Channel/Method Selection --}}
                    <div class="rounded-[28px] border border-slate-200 bg-white/95 p-7 shadow-[0_35px_80px_rgba(15,23,42,0.12)] dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-500">Langkah 2</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">Pilih Metode Pembayaran</h2>

                        @if($selectedGateway === 'midtrans')
                        <div class="mt-6 space-y-6">
                            @foreach($channelGroups as $cg)
                                <div>
                                    <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                                        @if($cg['icon'] === 'bank')
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 10v11M12 10v11M16 10v11" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @elseif($cg['icon'] === 'wallet')
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <rect x="2" y="7" width="20" height="14" rx="2"/>
                                                <path d="M16 14a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM2 11h20M7 3l-5 4M17 3l5 4" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @else
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-linecap="round" stroke-linejoin="round"/>
                                                <polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @endif
                                        {{ $cg['label'] }}
                                    </p>
                                    <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($cg['channels'] as $channel)
                                            @php
                                                $isSelected = $selectedChannel === $channel->value;
                                            @endphp
                                            <button
                                                type="button"
                                                wire:click="selectChannel('{{ $channel->value }}')"
                                                wire:key="ch-{{ $channel->value }}"
                                                class="flex items-center gap-3 rounded-2xl border px-4 py-3.5 text-left transition hover:-translate-y-0.5 {{ $isSelected ? 'border-slate-900 bg-slate-900 text-white shadow-lg shadow-slate-900/20 dark:border-white dark:bg-white dark:text-slate-900' : 'border-slate-200 bg-white/70 text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-200 dark:hover:border-slate-600' }}"
                                            >
                                                @if(in_array($channel->value, ['BCA_VA','BNI_VA','BRI_VA','PERMATA_VA','MANDIRI_BILL']))
                                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $isSelected ? 'bg-white/20 dark:bg-slate-900/20' : 'bg-slate-100 dark:bg-slate-800' }}">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 10v11M16 10v11" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </span>
                                                @elseif($channel->value === 'QRIS')
                                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $isSelected ? 'bg-white/20 dark:bg-slate-900/20' : 'bg-slate-100 dark:bg-slate-800' }}">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                                                            <path d="M14 14h2v2h-2zM18 14v2M18 18h2M14 18v2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </span>
                                                @elseif($channel->value === 'GOPAY')
                                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $isSelected ? 'bg-white/20 dark:bg-slate-900/20' : 'bg-slate-100 dark:bg-slate-800' }}">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <rect x="2" y="7" width="20" height="14" rx="2"/>
                                                            <path d="M16 14a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM2 11h20" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </span>
                                                @else
                                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $isSelected ? 'bg-white/20 dark:bg-slate-900/20' : 'bg-slate-100 dark:bg-slate-800' }}">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </span>
                                                @endif

                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-semibold leading-tight">{{ $channel->shortLabel() }}</p>
                                                    <p class="mt-0.5 truncate text-xs {{ $isSelected ? 'text-white/70 dark:text-slate-700' : 'text-slate-400' }}">{{ $channel->label() }}</p>
                                                </div>

                                                @if($isSelected)
                                                    <svg class="ml-auto h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path d="M20 6L9 17l-5-5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @else
                        {{-- Duitku methods --}}
                        <div class="mt-6">
                            {{-- Skeleton saat memuat metode Duitku --}}
                            <div wire:loading wire:target="selectGateway,loadDuitkuMethods" class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                                @for($i=0; $i<6; $i++)
                                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white/70 px-4 py-3.5 dark:border-slate-700 dark:bg-slate-900/40 animate-pulse">
                                        <span class="inline-flex h-8 w-12 shrink-0 rounded-xl bg-slate-200 dark:bg-slate-800"></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="h-3 w-28 rounded bg-slate-200 dark:bg-slate-800"></div>
                                            <div class="mt-2 h-2.5 w-20 rounded bg-slate-200 dark:bg-slate-800"></div>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            {{-- Daftar metode setelah termuat --}}
                            <div wire:loading.remove wire:target="selectGateway,loadDuitkuMethods">
                                @if(empty($duitkuMethods))
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-300">
                                        Memuat metode pembayaran...
                                    </div>
                                @else
                                <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($duitkuMethods as $m)
                                        @php $isSel = $selectedDuitkuMethod === ($m['method'] ?? ''); @endphp
                                        <button type="button"
                                            wire:click="$set('selectedDuitkuMethod','{{ $m['method'] }}')"
                                            class="flex items-center gap-3 rounded-2xl border px-4 py-3.5 text-left transition hover:-translate-y-0.5 {{ $isSel ? 'border-slate-900 bg-slate-900 text-white shadow-lg shadow-slate-900/20 dark:border-white dark:bg-white dark:text-slate-900' : 'border-slate-200 bg-white/70 text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-200' }}">
                                            <span class="inline-flex h-8 w-12 shrink-0 items-center justify-center rounded-xl overflow-hidden {{ $isSel ? 'bg-white/20 dark:bg-slate-900/20' : 'bg-slate-100 dark:bg-slate-800' }}">
                                                @if(!empty($m['image']))
                                                    <img src="{{ $m['image'] }}" alt="{{ $m['name'] ?? ($m['method'] ?? 'Metode') }}" class="h-6 w-11 object-contain" loading="lazy" referrerpolicy="no-referrer"/>
                                                @else
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="14" rx="2"/><path d="M7 17v2a2 2 0 0 0 2 2h6"/></svg>
                                                @endif
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold leading-tight">{{ $m['name'] ?? ($m['method'] ?? '-') }}</p>
                                                <p class="mt-0.5 truncate text-xs {{ $isSel ? 'text-white/70 dark:text-slate-700' : 'text-slate-400' }}">Biaya: {{ $m['fee_formatted'] ?? 'Gratis' }}</p>
                                            </div>
                                            @if($isSel)
                                                <svg class="ml-auto h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 6L9 17l-5-5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            @if($selectedGateway === 'duitku' && $charged)
                                @if(!empty($duitkuPaymentUrl))
                                    <a href="{{ $duitkuPaymentUrl }}" target="_blank" rel="noreferrer"
                                       class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:-translate-y-0.5">
                                        <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Bayar Sekarang
                                    </a>
                                @endif
                                <button type="button" wire:click="$refresh"
                                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800/40">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M12 8v4l3 3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 3a9 9 0 1 1-9 9" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Cek Status Pembayaran
                                </button>
                            @else
                                <button
                                    type="button"
                                    wire:click="submitPayment"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-70 cursor-not-allowed"
                                    wire:target="submitPayment"
                                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:-translate-y-0.5">
                                    <svg wire:loading.remove wire:target="submitPayment" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg wire:loading wire:target="submitPayment" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"/>
                                    </svg>
                                    <span wire:loading.remove wire:target="submitPayment">Bayar Sekarang</span>
                                    <span wire:loading wire:target="submitPayment">Memproses...</span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="cancelOrder"
                                    wire:confirm="Yakin ingin membatalkan pesanan ini?"
                                    wire:loading.attr="disabled"
                                    wire:target="cancelOrder"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3.5 text-sm font-medium text-slate-600 transition hover:border-red-200 hover:text-red-600 dark:border-slate-700 dark:text-slate-400 dark:hover:text-red-400">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M18 6L6 18M6 6l12 12" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    Batalkan Pesanan
                                </button>
                            @endif
                        </div>
                    </div>

                    @elseif($pageState === 'payment_pending' && $paymentInstructions)
                    {{-- Payment Instructions --}}
                    <div class="rounded-[28px] border border-slate-200 bg-white/95 p-7 shadow-[0_35px_80px_rgba(15,23,42,0.12)] dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-500">Instruksi Pembayaran</p>
                                <h2 class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">
                                    Bayar via {{ $paymentTitle ?? (\App\Enums\PaymentChannel::from($selectedChannel)->label()) }}
                                </h2>
                            </div>
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                                Bayar sebelum {{ $expiredAt }}
                            </div>
                        </div>

                        <div class="mt-6">
                            @php
                                $instrType = $paymentInstructions['type'] ?? '';
                            @endphp

                            {{-- Virtual Account (Midtrans bank_transfer/echannel) atau Duitku 'va' --}}
                            @if($instrType === 'bank_transfer' || $instrType === 'echannel' || $instrType === 'va')
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-5 dark:border-slate-800 dark:bg-slate-900/60">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                                        @if($instrType === 'echannel') Kode Bayar Mandiri @else Nomor Virtual Account @endif
                                    </p>

                                    @if($instrType === 'echannel')
                                        <div class="mt-3 space-y-2">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">Kode Perusahaan</p>
                                                    <p class="text-xl font-mono font-bold tracking-widest text-slate-900 dark:text-white">{{ $paymentInstructions['biller_code'] ?? '-' }}</p>
                                                </div>
                                            </div>
                                            <div x-data="{ copied: false }">
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Kode Tagihan</p>
                                                <div class="flex items-center gap-3">
                                                    <p class="text-2xl font-mono font-bold tracking-widest text-slate-900 dark:text-white">{{ $paymentInstructions['bill_key'] ?? '-' }}</p>
                                                    <button
                                                        @click="navigator.clipboard.writeText('{{ $paymentInstructions['bill_key'] ?? '' }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                        <svg x-show="!copied" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="9" y="9" width="13" height="13" rx="2" stroke-width="1.5"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-width="1.5"/></svg>
                                                        <svg x-show="copied" class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                        <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div x-data="{ copied: false }" class="flex items-center gap-3 mt-3">
                                            <p class="text-2xl font-mono font-bold tracking-widest text-slate-900 dark:text-white">{{ $paymentInstructions['va_number'] ?? ($paymentInstructions['vaNumber'] ?? '-') }}</p>
                                            <button
                                                @click="navigator.clipboard.writeText('{{ $paymentInstructions['va_number'] ?? ($paymentInstructions['vaNumber'] ?? '') }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                <svg x-show="!copied" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="9" y="9" width="13" height="13" rx="2" stroke-width="1.5"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-width="1.5"/></svg>
                                                <svg x-show="copied" class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                            </button>
                                        </div>
                                        @if(!empty($paymentInstructions['bank']))
                                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Bank {{ $paymentInstructions['bank'] ?? '' }}</p>
                                        @endif
                                    @endif
                                </div>

                                {{-- VA Steps --}}
                                <div class="mt-5">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Cara pembayaran</p>
                                    <ol class="mt-3 space-y-3">
                                        @if($instrType === 'echannel')
                                            @foreach(['Buka aplikasi Mandiri Online atau kunjungi ATM Mandiri.', 'Pilih menu "Bayar" → "Multi Payment".', 'Masukkan Kode Perusahaan dan Kode Tagihan di atas.', 'Konfirmasi detail tagihan dan selesaikan pembayaran.', 'Simpan bukti bayar untuk arsip.'] as $idx => $step)
                                                <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-white dark:text-slate-900">{{ $idx + 1 }}</span>
                                                    <span>{{ $step }}</span>
                                                </li>
                                            @endforeach
                                        @else
                                            @foreach(['Buka aplikasi m-banking, ATM, atau internet banking bank kamu.', 'Pilih menu Transfer / Bayar → Virtual Account.', 'Masukkan nomor VA di atas persis tanpa spasi.', 'Masukkan nominal sesuai total tagihan: Rp ' . number_format($transaction->amount, 0, ',', '.') . '.', 'Konfirmasi dan selesaikan pembayaran. Status diperbarui otomatis.'] as $idx => $step)
                                                <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-white dark:text-slate-900">{{ $idx + 1 }}</span>
                                                    <span>{{ $step }}</span>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ol>
                                </div>

                            {{-- QRIS (Midtrans/ Duitku) --}}
                            @elseif($instrType === 'qris')
                                <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-start">
                                    @if(!empty($paymentInstructions['qr_url']))
                                        <div class="shrink-0 rounded-2xl border border-slate-200 bg-white p-3 shadow-md dark:border-slate-700">
                                            <img src="{{ $paymentInstructions['qr_url'] }}" alt="QRIS Code" class="h-44 w-44 rounded-xl object-contain">
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Cara pembayaran</p>
                                        <ol class="mt-3 space-y-3">
                                            @foreach(['Buka aplikasi e-wallet atau mobile banking yang mendukung QRIS (GoPay, OVO, DANA, ShopeePay, dll).', 'Pilih menu "Scan" atau "Bayar via QRIS".', 'Arahkan kamera ke kode QR di atas.', 'Konfirmasi nominal Rp ' . number_format($transaction->amount, 0, ',', '.') . ' dan selesaikan transaksi.', 'Status order diperbarui otomatis setelah konfirmasi.'] as $idx => $step)
                                                <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-white dark:text-slate-900">{{ $idx + 1 }}</span>
                                                    <span>{{ $step }}</span>
                                                </li>
                                            @endforeach
                                        </ol>
                                        <p class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                                            QRIS berlaku lintas e-wallet. Pastikan saldo mencukupi sebelum scan.
                                        </p>
                                    </div>
                                </div>

                            {{-- GoPay (khusus Midtrans) --}}
                            @elseif($instrType === 'gopay')
                                <div class="space-y-4">
                                    @if(!empty($paymentInstructions['qr_url']))
                                        <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-start">
                                            <div class="shrink-0 rounded-2xl border border-slate-200 bg-white p-3 shadow-md dark:border-slate-700">
                                                <img src="{{ $paymentInstructions['qr_url'] }}" alt="GoPay QR" class="h-44 w-44 rounded-xl object-contain">
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Scan QR atau klik tombol berikut:</p>
                                                @if(!empty($paymentInstructions['deeplink']))
                                                    <a href="{{ $paymentInstructions['deeplink'] }}" class="mt-3 inline-flex items-center gap-2 rounded-2xl bg-green-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-green-500/30 transition hover:-translate-y-0.5">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke-width="1.5" stroke-linecap="round"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke-width="1.5" stroke-linecap="round"/></svg>
                                                        Buka GoPay
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        @if(!empty($paymentInstructions['deeplink']))
                                            <a href="{{ $paymentInstructions['deeplink'] }}" class="inline-flex items-center gap-2 rounded-2xl bg-green-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-green-500/30 transition hover:-translate-y-0.5">
                                                Buka GoPay
                                            </a>
                                        @endif
                                    @endif
                                </div>

                            {{-- Minimarket (khusus Midtrans) --}}
                            @elseif($instrType === 'cstore')
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-5 dark:border-slate-800 dark:bg-slate-900/60">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Kode Pembayaran {{ $paymentInstructions['store'] ?? '' }}</p>
                                    <div x-data="{ copied: false }" class="flex items-center gap-3 mt-3">
                                        <p class="text-3xl font-mono font-bold tracking-widest text-slate-900 dark:text-white">{{ $paymentInstructions['payment_code'] ?? '-' }}</p>
                                        <button
                                            @click="navigator.clipboard.writeText('{{ $paymentInstructions['payment_code'] ?? '' }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            <svg x-show="!copied" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="9" y="9" width="13" height="13" rx="2" stroke-width="1.5"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-width="1.5"/></svg>
                                            <svg x-show="copied" class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-5">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Cara pembayaran</p>
                                    <ol class="mt-3 space-y-3">
                                        @foreach(['Kunjungi kasir ' . ($paymentInstructions['store'] ?? 'minimarket') . ' terdekat.', 'Beritahu kasir kamu ingin bayar tagihan via Midtrans / e-commerce.', 'Berikan kode pembayaran di atas kepada kasir.', 'Bayar sesuai tagihan: Rp ' . number_format($transaction->amount, 0, ',', '.') . '.', 'Simpan struk sebagai bukti pembayaran.'] as $idx => $step)
                                            <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white dark:bg-white dark:text-slate-900">{{ $idx + 1 }}</span>
                                                <span>{{ $step }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif
                        </div>

                        {{-- Expiry warning --}}
                        <div class="mt-6 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-5 py-3.5 text-sm text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-200">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
                                <path d="M12 6v6l4 2" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <span>Selesaikan pembayaran sebelum <strong>{{ $expiredAt }}</strong>. Jika melewati batas waktu, slot otomatis dilepas.</span>
                        </div>
                    </div>

                    {{-- Refresh notice --}}
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/80 px-5 py-3.5 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Status pembayaran diperbarui otomatis setelah pembayaran ini dikonfirmasi.
                        </div>
                        <button wire:click="$refresh" class="shrink-0 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            Cek Status
                        </button>
                    </div>

                    @elseif($pageState === 'paid')
                    {{-- PAID STATE --}}
                    <div class="rounded-[28px] border border-emerald-200 bg-gradient-to-br from-emerald-50 to-sky-50 p-8 shadow-lg dark:border-emerald-500/30 dark:from-emerald-500/10 dark:to-sky-500/10">
                        <div class="flex items-center gap-4">
                            <div class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 shadow-lg shadow-emerald-500/30">
                                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M20 6L9 17l-5-5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Pembayaran Berhasil</p>
                                <h2 class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">Terima kasih!</h2>
                                @if($deliveryItem)
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Pembayaranmu sudah dikonfirmasi dan akses layanan sudah <strong>aktif</strong>.</p>
                                @else
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Pembayaranmu sudah dikonfirmasi. Admin akan segera mengaktifkan seat.</p>
                                @endif
                            </div>
                        </div>
                        @if(!$deliveryItem)
                            <div class="mt-6 rounded-2xl border border-emerald-200 bg-white/70 p-5 text-sm text-slate-600 dark:border-emerald-500/20 dark:bg-slate-900/50 dark:text-slate-300">
                                <p class="font-semibold text-slate-900 dark:text-white">Apa yang terjadi selanjutnya?</p>
                                <ol class="mt-3 space-y-2">
                                    @foreach(['Admin menerima notifikasi pembayaranmu.', 'Seat akan diaktifkan maksimal 30 menit setelah semua slot terisi.', 'Kamu akan mendapat info akses dari admin via WhatsApp / email.'] as $i => $step)
                                        <li class="flex items-start gap-2">
                                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-xs font-bold text-white">{{ $i + 1 }}</span>
                                            <span>{{ $step }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noreferrer" class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:-translate-y-0.5">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.486 2 2 6.019 2 11c0 2.281.964 4.363 2.561 5.953L4 22l5.176-1.541A10.28 10.28 0 0 0 12 20c5.514 0 10-4.019 10-9s-4.486-9-10-9Zm4.971 12.203c-.208.588-1.229 1.123-1.701 1.193-.453.067-1.004.096-1.621-.103-.374-.12-.853-.275-1.463-.538-2.577-1.115-4.253-3.764-4.381-3.939-.129-.176-1.047-1.396-1.047-2.662 0-1.266.662-1.887.897-2.147.235-.258.515-.323.686-.323.172 0 .343.002.494.009.159.008.37-.059.58.442.208.5.708 1.73.77 1.856.06.127.1.275.018.451-.081.176-.122.274-.24.421-.118.146-.25.327-.356.44-.118.127-.24.265-.103.519.136.254.6.979 1.288 1.587.887.796 1.633 1.045 1.882 1.162.248.117.392.101.536-.061.143-.162.617-.719.783-.965.166-.245.33-.205.558-.12.228.086 1.456.69 1.706.815.25.127.414.19.475.297.06.107.06.619-.149 1.207Z"/>
                                </svg>
                                Hubungi Admin
                            </a>
                        @else
                            <a href="{{ route('member.deliveries.show', $deliveryItem) }}" wire:navigate class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:-translate-y-0.5">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M9 5l7 7-7 7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Buka Delivery
                            </a>
                        @endif
                    </div>

                    @elseif($pageState === 'terminal')
                    {{-- TERMINAL STATE: cancelled/expired/failed --}}
                    <div class="rounded-[28px] border border-slate-200 bg-white/95 p-8 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center gap-4">
                            <div class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                                <svg class="h-7 w-7 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M18 6L6 18M6 6l12 12" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Order Tidak Aktif</p>
                                <h2 class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ $statusInfo['label'] }}</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Order ini sudah tidak dapat diproses lebih lanjut.</p>
                            </div>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 dark:bg-white dark:text-slate-900">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M9 5l7 7-7 7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Pesan Lagi
                            </a>
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:-translate-y-0.5 dark:border-slate-700 dark:text-slate-300">
                                Kontak Admin
                            </a>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- RIGHT: Order Summary --}}
                <div class="space-y-5">
                    {{-- Summary Card --}}
                    <div class="rounded-[28px] border border-slate-200 bg-white/95 p-6 shadow-[0_35px_80px_rgba(15,23,42,0.12)] dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">Ringkasan Pesanan</p>

                        <div class="mt-5 flex items-start gap-4">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl bg-slate-100 shadow dark:bg-slate-800">
                                <img src="{{ $productImage }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">{{ $product->categories->pluck('name')->implode(', ') ?: 'Patungan' }}</p>
                                <p class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $product->name }}</p>
                                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $productItem->name }}</p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Grup</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $group->name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Durasi</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $product->duration ?? 30 }} hari</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Kuota paket</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $productItem->max_users }} user</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400">Metode bayar</span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ PaymentChannel::from($selectedChannel)->shortLabel() }}</span>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-slate-100 pt-5 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-500 dark:text-slate-400">Kode Order</span>
                                <span class="font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $transaction->order_code }}</span>
                            </div>
                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                                    <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format((int) $productItem->price_per_user, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400">Diskon</span>
                                    <span class="font-medium text-slate-900 dark:text-white">- Rp {{ number_format((int) ($transaction->discount ?? 0), 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400">Biaya admin</span>
                                    <span class="font-medium text-slate-900 dark:text-white">+ Rp {{ number_format((int) ($transaction->fee ?? 0), 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="mt-4 rounded-2xl border border-slate-900 bg-slate-900 px-5 py-4 dark:border-white dark:bg-white">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Total</p>
                                <p class="mt-1 text-3xl font-bold text-white dark:text-slate-900">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Untuk 1 slot selama {{ $product->duration ?? 30 }} hari</p>
                            </div>
                        </div>
                    </div>

                    {{-- Help card removed per requirement when delivery is available/streamlined UX --}}
                </div>

            </div>
        </div>
    </main>

    @php
        $footerColumns = [
            [
                'title' => 'Layanan',
                'items' => [
                    ['label' => 'Halaman Produk', 'href' => route('products.show', $product->slug)],
                    ['label' => 'Beranda', 'href' => route('home')],
                ],
            ],
            [
                'title' => 'Bantuan',
                'items' => [
                    ['label' => 'Cara pembayaran', 'href' => route('home') . '#faq'],
                    ['label' => 'Kontak admin', 'href' => 'https://wa.me/6281234567890'],
                ],
            ],
        ];
    @endphp
    <x-home.footer :columns="$footerColumns" />
</div>
