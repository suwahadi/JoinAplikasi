@php
    $durationDays = $product->duration ?? 30;
    $priceText = $selectedPackage ? 'Rp ' . number_format($selectedPackage['price'], 0, ',', '.') : 'Segera hadir';
    $slotCopy = $selectedPackage && $selectedPackage['max_users'] ? $selectedPackage['max_users'] . ' pengguna/grup' : 'Paket segera tersedia';
    $categoryLabel = $product->categories->pluck('name')->implode(', ') ?: 'Produk patungan';
    $footerColumns = [
        [
            'title' => 'Layanan Populer',
            'items' => [
                ['label' => 'ChatGPT Plus', 'href' => route('home') . '#layanan'],
                ['label' => 'Claude AI Pro', 'href' => route('home') . '#layanan'],
                ['label' => 'Netflix 1P 1U', 'href' => route('home') . '#layanan'],
                ['label' => 'Spotify Platinum', 'href' => route('home') . '#layanan'],
            ],
        ],
        [
            'title' => 'Panduan',
            'items' => [
                ['label' => 'Cara patungan', 'href' => route('home') . '#grup-terakhir'],
                ['label' => 'Metode pembayaran', 'href' => route('home') . '#faq'],
                ['label' => 'Kontak admin', 'href' => 'https://wa.me/6281234567890'],
            ],
        ],
        [
            'title' => 'Perusahaan',
            'items' => [
                ['label' => 'Tentang kami', 'href' => route('home') . '#tentang'],
                ['label' => 'Karier', 'href' => '#karier'],
                ['label' => 'Press kit', 'href' => '#press'],
            ],
        ],
    ];
@endphp
<div class="relative isolate overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-[-200px] z-0 mx-auto h-[420px] w-[720px] rounded-full blur-[140px]" style="background: radial-gradient(circle at 20% 20%, rgba(255,180,0,0.5), transparent), radial-gradient(circle at 80% 0%, rgba(255,64,129,0.35), transparent);"></div>

    <x-layouts.marketing-header :prefix="route('home')" />

    <main class="relative z-10 space-y-12 pt-32 pb-24">
        <section class="mx-auto max-w-6xl px-6 pb-16">
    <div class="flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 font-medium text-slate-700 shadow-sm transition hover:-translate-y-0.5 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200" wire:navigate>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M15 19l-7-7 7-7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Kembali
        </a>
        <span>/</span>
        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $product->name }}</span>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <div class="relative overflow-hidden rounded-[32px] border border-white/40 bg-white/90 p-8 shadow-[0_45px_120px_rgba(15,23,42,0.18)] backdrop-blur dark:border-slate-800 dark:bg-slate-900">
            <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(circle at 10% 20%, rgba(251,191,36,.35), transparent 55%), radial-gradient(circle at 80% 0%, rgba(244,63,94,.25), transparent 50%);"></div>
            <div class="relative z-10">
                <div class="flex flex-wrap items-start gap-4">
                    <div class="h-16 w-16 overflow-hidden rounded-3xl bg-white/80 shadow-lg shadow-orange-500/30">
                        <img src="{{ $productImage }}" alt="Logo {{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-500">{{ $categoryLabel }}</p>
                        <h1 class="mt-2 text-4xl font-semibold text-slate-900 dark:text-white">{{ $product->name }}</h1>
                        <p class="mt-4 text-base text-slate-600 dark:text-slate-300">{{ $product->description ?? 'Gabung patungan resmi yang diawasi admin. Notifikasi seat & pembayaran otomatis dari dashboard ' . config('app.name') . '.' }}</p>
                    </div>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/60 bg-white/90 p-4 text-slate-600 shadow-inner dark:border-slate-800 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide">Mulai dari</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $priceText }}</p>
                        <p class="text-sm text-slate-500">/{{ $durationDays }} hari</p>
                    </div>
                    <div class="rounded-2xl border border-white/60 bg-white/90 p-4 text-slate-600 shadow-inner dark:border-slate-800 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide">Kuota per grup</p>
                        <p class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">{{ $slotCopy }}</p>
                        <p class="text-sm text-slate-500">Admin bantu isi otomatis</p>
                    </div>
                    <div class="rounded-2xl border border-white/60 bg-white/90 p-4 text-slate-600 shadow-inner dark:border-slate-800 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide">Grup terdaftar</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ number_format($groupStats['total']) }}</p>
                        <p class="text-sm text-slate-500">{{ $groupStats['active'] }} aktif · {{ $groupStats['full'] }} penuh</p>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    @foreach($infoBadges as $badge)
                        <span class="inline-flex items-center rounded-full border border-white/70 bg-white/80 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">{{ $badge }}</span>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-4 lg:grid-cols-3">
                    @foreach($serviceHighlights as $highlight)
                        <div class="rounded-2xl border border-white/60 bg-white/90 p-4 text-slate-600 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $highlight['title'] }}</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $highlight['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-[28px] border border-slate-200 bg-white/95 p-6 shadow-[0_35px_80px_rgba(15,23,42,0.15)] dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-500">Pilih paket</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Slot yang tersedia</h2>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">{{ $groupStats['active'] }} grup aktif</span>
            </div>

            <div class="mt-6 space-y-3">
                @forelse($packages as $package)
                    @php($isActive = $selectedPackage && $selectedPackage['id'] === $package['id'])
                    <button
                        type="button"
                        wire:click="selectPackage({{ $package['id'] }})"
                        wire:key="package-{{ $package['id'] }}"
                        wire:loading.attr="disabled"
                        class="w-full rounded-2xl border px-4 py-4 text-left transition {{ $isActive ? 'border-transparent bg-slate-900 text-white shadow-xl shadow-slate-900/20 dark:bg-white dark:text-slate-900' : 'border-slate-200 bg-white/80 text-slate-700 hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-200' }}"
                    >
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <div>
                                <p class="font-semibold">{{ $package['name'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $package['max_users'] }} user · {{ $durationDays }} hari</p>
                            </div>
                            <p class="text-base font-semibold">Rp {{ number_format($package['price'], 0, ',', '.') }}</p>
                        </div>
                    </button>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-300">
                        Paket sedang dipersiapkan. Aktifkan notifikasi di dashboard untuk update otomatis.
                    </div>
                @endforelse
            </div>

            <div class="mt-8 rounded-3xl border border-slate-900 bg-slate-900 px-5 py-5 text-white shadow-inner shadow-black/20 dark:border-white dark:bg-white dark:text-slate-900">
                <div class="flex items-center justify-between text-sm font-semibold">
                    <p>Harga per user</p>
                    <p>{{ $priceText }}</p>
                </div>
                <div class="mt-2 flex items-center justify-between text-sm text-slate-300 dark:text-slate-500">
                    <p>Durasi siklus</p>
                    <p>{{ $durationDays }} hari</p>
                </div>
                <div class="mt-2 flex items-center justify-between text-sm text-slate-300 dark:text-slate-500">
                    <p>Kuota paket</p>
                    <p>{{ $slotCopy }}</p>
                </div>
                <div class="mt-4 rounded-2xl bg-white/10 px-4 py-3 text-sm text-slate-200 dark:bg-slate-900/10 dark:text-slate-600">
                    Aktivasi seat dilakukan setelah semua slot terisi dan pembayaran via Midtrans terkonfirmasi.
                </div>
                @if(session('order_error'))
                    <div class="mt-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        {{ session('order_error') }}
                    </div>
                @endif
                <button
                    type="button"
                    wire:click="orderNow"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 cursor-not-allowed"
                    wire:target="orderNow"
                    class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/30 transition hover:-translate-y-0.5 dark:bg-slate-900 dark:text-white">
                    <svg wire:loading.remove wire:target="orderNow" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.3 2.3a1 1 0 0 0 .7 1.7H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-10 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg wire:loading wire:target="orderNow" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"/>
                    </svg>
                    <span wire:loading.remove wire:target="orderNow">Pesan Sekarang</span>
                    <span wire:loading wire:target="orderNow">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    </section>

    <section class="mx-auto max-w-6xl px-6">
    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-[28px] border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-semibold text-slate-900 dark:text-white">Informasi Produk</h3>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-200">Update harian</span>
            </div>
            <div class="mt-6 space-y-6 text-sm text-slate-600 dark:text-slate-400">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Deskripsi</p>
                    <p class="mt-2 text-base text-slate-600 dark:text-slate-300">{{ $product->description ?? 'Layanan premium resmi yang dibagikan ke komunitas terpercaya. ' . config('app.name') . ' menanggung manajemen akun, pembayaran rutin, serta bantuan teknis ketika kamu butuh upgrade.' }}</p>
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-300">Fitur layanan</p>
                    <ul class="mt-3 space-y-3">
                        @foreach($serviceHighlights as $highlight)
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">✔</span>
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">{{ $highlight['title'] }}</p>
                                    <p class="text-slate-600 dark:text-slate-400">{{ $highlight['body'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                    <p class="font-semibold">Catatan penting</p>
                    <p class="mt-1">Sistem pre-order otomatis. Aktivasi dilakukan maksimal 30 menit setelah grup penuh dan seluruh pembayaran terselesaikan.</p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[28px] border border-slate-200 bg-white/90 p-6 shadow-lg shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-300">Ringkasan grup</p>
                <div class="mt-4 grid gap-4">
                    <div class="rounded-2xl border border-slate-100 bg-white/80 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Total grup</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($groupStats['total']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white/80 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Aktif sekarang</p>
                        <p class="text-2xl font-semibold text-emerald-600 dark:text-emerald-300">{{ number_format($groupStats['active']) }} grup</p>
                    </div>
                    <div class="rounded-2xl border border-slate-100 bg-white/80 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Grup penuh</p>
                        <p class="text-2xl font-semibold text-amber-500">{{ number_format($groupStats['full']) }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-[28px] border border-slate-200 bg-gradient-to-br from-emerald-500 to-sky-500 p-6 text-white shadow-lg shadow-emerald-500/40 dark:border-slate-800">
                <p class="text-sm font-semibold uppercase tracking-[0.2em]">Butuh bantuan?</p>
                <p class="mt-2 text-2xl font-semibold">Tim admin siap bantu lewat WhatsApp</p>
                <p class="mt-2 text-sm text-white/80">Konsultasi paket, cek status seat, atau minta dibuatkan grup privat kapan pun.</p>
                <a href="https://wa.me/6281234567890" target="_blank" rel="noreferrer" class="mt-5 inline-flex w-full items-center justify-center rounded-2xl bg-white/20 px-5 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/30">
                    <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.486 2 2 6.019 2 11c0 2.281.964 4.363 2.561 5.953L4 22l5.176-1.541A10.28 10.28 0 0 0 12 20c5.514 0 10-4.019 10-9s-4.486-9-10-9Zm4.971 12.203c-.208.588-1.229 1.123-1.701 1.193-.453.067-1.004.096-1.621-.103-.374-.12-.853-.275-1.463-.538-2.577-1.115-4.253-3.764-4.381-3.939-.129-.176-1.047-1.396-1.047-2.662 0-1.266.662-1.887.897-2.147.235-.258.515-.323.686-.323.172 0 .343.002.494.009.159.008.37-.059.58.442.208.5.708 1.73.77 1.856.06.127.1.275.018.451-.081.176-.122.274-.24.421-.118.146-.25.327-.356.44-.118.127-.24.265-.103.519.136.254.6.979 1.288 1.587.887.796 1.633 1.045 1.882 1.162.248.117.392.101.536-.061.143-.162.617-.719.783-.965.166-.245.33-.205.558-.12.228.086 1.456.69 1.706.815.25.127.414.19.475.297.06.107.06.619-.149 1.207Z" />
                    </svg>
                    Kontak Admin
                </a>
            </div>
        </div>
    </div>
    </section>

    <section class="mx-auto max-w-6xl px-6">
    <div class="rounded-[32px] border border-slate-200 bg-white/95 p-8 shadow-2xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-500">Grup patungan tersedia</p>
                <h3 class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">Pilih grup terbaru {{ $product->name }}</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Slot diperbarui realtime beserta status pembayaran Midtrans.</p>
            </div>
            <div class="flex flex-wrap gap-3 text-sm">
                <span class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">Semua ({{ $groupStats['total'] }})</span>
                <span class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 font-semibold text-emerald-600 dark:border-slate-700 dark:text-emerald-300">Aktif ({{ $groupStats['active'] }})</span>
            </div>
        </div>

        <div class="mt-8 grid gap-5 lg:grid-cols-2">
            @forelse($groups as $group)
                <div class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-lg shadow-slate-900/5 transition hover:-translate-y-0.5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Kuota {{ $group['max_users'] }} user · {{ $durationDays }} hari</p>
                            <h4 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $group['name'] }}</h4>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $group['status_class'] }}">{{ $group['status_label'] }}</span>
                            <div class="mt-2 rounded-full px-3 py-1 text-xs font-medium {{ $group['tag']['class'] }}">{{ $group['tag']['label'] }}</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm font-semibold text-slate-700 dark:text-slate-200">
                            <p>{{ $group['filled'] }} / {{ $group['max_users'] }} slot terisi</p>
                            <span>{{ $group['progress'] }}%</span>
                        </div>
                        <div class="mt-3 h-2 rounded-full bg-slate-100 dark:bg-slate-800">
                            <span class="block h-full rounded-full bg-gradient-to-r from-orange-500 to-pink-500" style="width: {{ $group['progress'] }}%"></span>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-100 bg-white/80 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <span>Anggota</span>
                            <span>Live update</span>
                        </div>
                        <ul class="mt-4 space-y-2">
                            @foreach($group['members'] as $index => $member)
                                <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-200">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-200">{{ $index + 1 }}</span>
                                    <span>{{ $member }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-6 flex flex-col gap-4 text-sm text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm">Harga per user</p>
                            <p class="text-xl font-semibold text-slate-900 dark:text-white">Rp {{ number_format($group['price'] ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <button
                            type="button"
                            wire:click="joinGroup({{ $group['id'] }})"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-70 cursor-not-allowed"
                            wire:target="joinGroup({{ $group['id'] }})"
                            wire:key="join-btn-{{ $group['id'] }}"
                            @if($group['status'] !== \App\Enums\GroupStatus::AVAILABLE->value) disabled @endif
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2 font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-white dark:text-slate-900">
                            <svg wire:loading.remove wire:target="joinGroup({{ $group['id'] }})" class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M20 8v6M23 11h-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <svg wire:loading wire:target="joinGroup({{ $group['id'] }})" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"/>
                            </svg>
                            <span wire:loading.remove wire:target="joinGroup({{ $group['id'] }})">
                                {{ $group['status'] === \App\Enums\GroupStatus::AVAILABLE->value ? 'Gabung grup' : 'Grup penuh' }}
                            </span>
                            <span wire:loading wire:target="joinGroup({{ $group['id'] }})">Memproses...</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-200 bg-white/70 p-12 text-center text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-300">
                    Belum ada grup terbuka untuk layanan ini. Klik "Pesan Sekarang" untuk buat grup baru bersama admin.
                </div>
            @endforelse
        </div>
    </div>
        </section>
    </main>

    <x-home.footer :columns="$footerColumns" />
</div>
