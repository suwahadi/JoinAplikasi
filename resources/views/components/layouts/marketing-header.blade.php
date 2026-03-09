@props([
    'prefix' => '',
    'ctaHref' => '/login',
    'ctaLabel' => 'Login',
])

@php
    $brandName = config('app.name', 'Patungin.id');
    $brandInitial = mb_strtoupper(mb_substr($brandName, 0, 1, 'UTF-8'));
    $navLinks = [
        ['label' => 'Produk', 'anchor' => '#layanan'],
        ['label' => 'Grup Aktif', 'anchor' => '#grup-terakhir'],
        ['label' => 'FAQ', 'anchor' => '#faq'],
        ['label' => 'Tentang', 'anchor' => '#tentang'],
    ];
    $prefix = $prefix !== '' ? rtrim($prefix, '/') : '';
    $inPage = $prefix === '';
@endphp

<header class="fixed inset-x-0 top-0 z-40 border-b border-white/10 bg-white/80 backdrop-blur-xl dark:border-slate-800/60 dark:bg-slate-900/80">
    <div class="mx-auto flex h-20 max-w-6xl items-center justify-between px-6">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white" wire:navigate>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-rose-500 text-white">{{ $brandInitial }}</span>
            {{ $brandName }}
        </a>
        <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex dark:text-slate-200">
            @foreach($navLinks as $link)
                @php($href = $inPage ? $link['anchor'] : $prefix . $link['anchor'])
                <a href="{{ $href }}" class="transition hover:text-orange-500 dark:hover:text-orange-300">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="flex items-center gap-3">
            <button type="button" data-theme-toggle class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 dark:border-slate-700 dark:text-slate-100">
                <span data-theme-icon="sun" class="hidden">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.657-7.657l-1.414 1.414M7.757 16.243l-1.414 1.414m0-12.728l1.414 1.414m9.9 9.9l1.414 1.414" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </span>
                <span data-theme-icon="moon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </span>
                <span data-theme-label>Mode Gelap</span>
            </button>
            <a href="{{ $ctaHref }}" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/40 transition hover:-translate-y-0.5" wire:navigate>
                {{ $ctaLabel }}
            </a>
        </div>
    </div>
</header>
