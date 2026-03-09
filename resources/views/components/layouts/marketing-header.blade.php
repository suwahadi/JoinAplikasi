@props([
    'prefix' => '',
    'ctaHref' => '/login',
    'ctaLabel' => 'Login',
    'authUser' => null,
])

@php
    $authUser = $authUser ?? auth()->user();
    $brandName = config('app.name', 'JoinAplikasi');
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
                <!-- <span data-theme-label>Mode Gelap</span> -->
            </button>

            @if($authUser)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:-translate-y-0.5 dark:border-slate-700 dark:text-slate-200">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-rose-500 text-xs font-bold text-white">{{ mb_strtoupper(mb_substr($authUser->name, 0, 1, 'UTF-8')) }}</span>
                        <span class="hidden max-w-[120px] truncate sm:inline">{{ explode(' ', trim($authUser->name))[0] }}</span>
                        <svg class="h-3.5 w-3.5 text-slate-400 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 9l6 6 6-6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                        class="absolute right-0 top-full mt-2 w-56 origin-top-right rounded-2xl border border-slate-200 bg-white py-1 shadow-xl dark:border-slate-700 dark:bg-slate-900"
                    >
                        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $authUser->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $authUser->email }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('dashboard') }}" wire:navigate @click="open = false" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                                <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5" stroke-width="1.5"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1.5" stroke-width="1.5"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1.5" stroke-width="1.5"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1.5" stroke-width="1.5"/>
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('profile') }}" wire:navigate @click="open = false" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800">
                                <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="8" r="4" stroke-width="1.5"/>
                                    <path d="M4 20c0-3.866 3.582-7 8-7s8 3.134 8 7" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                Profil Saya
                            </a>
                        </div>
                        <div class="border-t border-slate-100 py-1 dark:border-slate-800">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-500 transition hover:bg-red-50 dark:hover:bg-red-500/10">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ $ctaHref }}" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-500/40 transition hover:-translate-y-0.5" wire:navigate>
                    {{ $ctaLabel }}
                </a>
            @endif
        </div>
    </div>
</header>
