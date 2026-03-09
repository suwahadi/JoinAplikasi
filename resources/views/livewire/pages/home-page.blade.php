@php
    $footerColumns = [
        [
            'title' => 'Layanan Populer',
            'items' => [
                ['label' => 'ChatGPT Plus', 'href' => '#layanan'],
                ['label' => 'Claude AI Pro', 'href' => '#layanan'],
                ['label' => 'Netflix 1P 1U', 'href' => '#layanan'],
                ['label' => 'Spotify Platinum', 'href' => '#layanan'],
            ],
        ],
        [
            'title' => 'Panduan',
            'items' => [
                ['label' => 'Cara patungan', 'href' => '#grup-terakhir'],
                ['label' => 'Metode pembayaran', 'href' => '#faq'],
                ['label' => 'Kontak admin', 'href' => 'https://wa.me/6281234567890'],
            ],
        ],
        [
            'title' => 'Perusahaan',
            'items' => [
                ['label' => 'Tentang kami', 'href' => '#tentang'],
                ['label' => 'Karier', 'href' => '#karier'],
                ['label' => 'Press kit', 'href' => '#press'],
            ],
        ],
    ];
@endphp

<div class="relative isolate overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-[-200px] z-0 mx-auto h-[420px] w-[720px] rounded-full blur-[140px]" style="background: radial-gradient(circle at 20% 20%, rgba(255,180,0,0.5), transparent), radial-gradient(circle at 80% 0%, rgba(255,64,129,0.35), transparent);"></div>

    <x-layouts.marketing-header />

    <main class="relative z-10 pt-28 pb-24">
        <section class="mx-auto max-w-6xl px-6">
            <div class="grid gap-12 rounded-[32px] border border-white/40 bg-white/80 p-10 shadow-[0_50px_120px_rgba(15,23,42,0.15)] backdrop-blur dark:border-slate-800 dark:bg-slate-900/70 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-orange-500">Patungan aplikasi premium</p>
                    <h1 class="mt-4 text-4xl font-semibold text-slate-900 sm:text-5xl dark:text-white">Nikmati fitur premium dengan harga patungan terjangkau</h1>
                    <p class="mt-6 text-lg text-slate-600 dark:text-slate-300">Gabung bersama ribuan kreator dan komunitas untuk berbagi biaya layanan digital favoritmu. Team kami bantu proses pembayaran, aktivasi seat, sampai pengingat tagihan.</p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @foreach($featureTags as $tag)
                            <span class="inline-flex items-center rounded-full border border-orange-200 bg-orange-50 px-4 py-1 text-xs font-medium text-orange-700 dark:border-orange-500/30 dark:bg-orange-500/10 dark:text-orange-200">{{ $tag }}</span>
                        @endforeach
                    </div>

                    <div class="mt-10 flex flex-wrap gap-4">
                        @foreach($ctaButtons as $cta)
                            <a href="{{ $cta['href'] }}" class="inline-flex items-center gap-2 rounded-2xl px-5 py-3 text-sm font-semibold transition {{ $cta['primary'] ? 'bg-slate-900 text-white shadow-xl shadow-slate-900/20 hover:-translate-y-0.5 dark:bg-white dark:text-slate-900' : 'border border-slate-200 text-slate-700 hover:-translate-y-0.5 dark:border-slate-700 dark:text-slate-100' }}">
                                {{ $cta['label'] }}
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M9 5l7 7-7 7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-12 grid gap-4 sm:grid-cols-3">
                        @foreach($stats as $stat)
                            <div class="rounded-2xl border border-slate-100 bg-white/80 p-4 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <p class="text-3xl font-semibold text-slate-900 dark:text-white">{{ $stat['value'] }}</p>
                                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-gradient-to-br from-orange-100 via-white to-white p-6 shadow-xl dark:border-slate-800 dark:from-slate-800 dark:via-slate-900 dark:to-slate-900">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-200">Paket favorit minggu ini</p>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-orange-500 dark:bg-slate-900/80">Live</span>
                        </div>
                        <p class="mt-4 text-2xl font-semibold text-slate-900 dark:text-white">Patungan ChatGPT Plus</p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Seat tersisa 2 dari 5. Aktivasi otomatis setelah pembayaran.</p>
                        <div class="mt-6">
                            <div class="flex justify-between text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                <span>Progress</span>
                                <span>80%</span>
                            </div>
                            <div class="mt-2 h-3 rounded-full bg-white/50 dark:bg-slate-800/80">
                                <span class="block h-3 rounded-full bg-gradient-to-r from-orange-500 to-red-500" style="width: 80%"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($heroPerks as $perk)
                            <div class="rounded-2xl border border-slate-100 bg-white/80 p-4 dark:border-slate-800 dark:bg-slate-900">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $perk['title'] }}</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $perk['body'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto mt-24 max-w-6xl px-6">
            <livewire:home.product-catalog />
        </section>

        <livewire:home.group-showcase />

        <section id="tentang" class="mx-auto mt-24 max-w-6xl px-6">
            <div class="grid gap-8 rounded-[28px] border border-slate-200 bg-white/80 p-10 shadow-xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-orange-500">Tentang kami</p>
                    <h3 class="mt-4 text-3xl font-semibold text-slate-900 dark:text-white">Patungin.id membantu komunitas berbagi biaya langganan digital secara aman.</h3>
                    <p class="mt-4 text-base text-slate-600 dark:text-slate-400">Seluruh pembayaran diproses lewat Midtrans dengan signature verification. Setiap grup memiliki admin pendamping yang memonitor aktivasi seat, mengelola slot anggota, hingga pengingat jatuh tempo.</p>
                </div>
                <ul class="space-y-4 text-sm text-slate-600 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-300">1</span>
                        Buat atau pilih grup publik sesuai layanan favoritmu, lihat detail slot dan harga transparan.
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-300">2</span>
                        Lakukan pembayaran via Midtrans. Sistem otomatis memberi tahu ketika kuota terpenuhi.
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-300">3</span>
                        Admin aktifkan seat, bagikan kredensial aman, dan kamu tinggal menikmati fitur premium.
                    </li>
                </ul>
            </div>
        </section>

        <section id="riwayat" class="mx-auto mt-24 max-w-6xl px-6">
            <div class="grid gap-8 rounded-[28px] border border-slate-200 bg-white/90 p-10 shadow-xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-orange-500">Siap mulai?</p>
                        <h3 class="mt-4 text-3xl font-semibold text-slate-900 dark:text-white">Cek patungan terakhir kamu atau mulai grup baru sekarang.</h3>
                        <p class="mt-3 text-base text-slate-600 dark:text-slate-400">Kami bantu pastikan slot terisi, pembayaran berhasil, sampai akun aktif. Semua otomatis dengan Midtrans.</p>
                    </div>
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <a href="/register" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 dark:bg-white dark:text-slate-900" wire:navigate>
                            Daftar Sekarang
                        </a>
                        <a href="/login" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-6 py-3 font-semibold text-slate-700 transition hover:-translate-y-0.5 dark:border-slate-700 dark:text-slate-200" wire:navigate>
                            Cek Patungan Saya
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="mx-auto mt-24 max-w-6xl px-6">
            <x-home.section-heading
                eyebrow="FAQ"
                title="Pertanyaan yang sering diajukan"
                subtitle="Masih bingung? Hubungi admin kapan pun lewat WhatsApp."
                align="left"
            />

            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Kapan grup dinyatakan aktif?</p>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Begitu slot terpenuhi dan pembayaran berhasil, Midtrans mengirim notifikasi ke sistem dan admin langsung aktivasi layanan paling lambat 30 menit.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Bagaimana bila grup batal?</p>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Saldo otomatis direfund ke metode pembayaran awal atau saldo dompet digital kamu maksimal 1x24 jam.</p>
                </div>
            </div>
        </section>
    </main>

    <x-home.footer :columns="$footerColumns" />
</div>

