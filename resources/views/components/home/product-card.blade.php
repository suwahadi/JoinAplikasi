@props([
    'title',
    'description' => null,
    'price' => null,
    'duration' => 30,
    'maxUsers' => null,
    'accent' => 'from-orange-500 to-rose-500',
    'badge' => null,
    'href' => null,
    'image' => null,
    'imageAlt' => null,
])

@php
    $initial = mb_strtoupper(mb_substr($title, 0, 2));
    $priceText = $price ? 'Rp ' . number_format($price, 0, ',', '.') : null;
    $usersCopy = $maxUsers ? 'Hingga ' . $maxUsers . ' akun' : 'Kuota fleksibel';
@endphp

<article {{ $attributes->class('group flex h-full flex-col justify-between rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-[0_15px_45px_rgba(15,23,42,0.08)] backdrop-blur dark:border-slate-800 dark:bg-slate-900/70') }}>
    <div>
        <div class="flex items-start gap-4">
            <div class="h-14 w-14 overflow-hidden rounded-2xl bg-gradient-to-br {{ $accent }} shadow-lg shadow-orange-500/30">
                @if($image)
                    <img
                        src="{{ $image }}"
                        alt="{{ $imageAlt ?? $title }}"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    >
                @else
                    <span class="flex h-full w-full items-center justify-center text-lg font-semibold uppercase text-white">{{ $initial }}</span>
                @endif
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                    @if($badge)
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">{{ $badge }}</span>
                    @endif
                </div>
                @if($description)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Mulai dari</p>
                @if($priceText)
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $priceText }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">/{{ $duration }} hari</p>
                @else
                    <p class="text-lg font-semibold text-slate-500 dark:text-slate-400">Segera hadir</p>
                @endif
            </div>
            <div class="rounded-2xl border border-dashed border-slate-200 p-3 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-300">
                <p class="font-semibold text-slate-700 dark:text-slate-100">{{ $usersCopy }}</p>
                <p>Garansi uang kembali & proteksi Midtrans</p>
            </div>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between gap-4 text-sm text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-2 font-medium text-slate-600 dark:text-slate-200">
            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
            Slot update realtime
        </div>
        @if($href)
            <a href="{{ $href }}" class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2 font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 dark:bg-white dark:text-slate-900">
                Lihat Grup
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @else
            <button type="button" class="inline-flex items-center rounded-2xl bg-slate-900 px-4 py-2 font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 dark:bg-white dark:text-slate-900">
                Lihat Grup Tersedia
                <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @endif
    </div>
</article>
