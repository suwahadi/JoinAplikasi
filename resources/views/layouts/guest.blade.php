<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'JoinAplikasi') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <script>
            (function () {
                const storageKey = 'joinaplikasi-theme';

                const persist = (value) => {
                    try {
                        window.localStorage.setItem(storageKey, value);
                    } catch (error) {
                        /* no-op */
                    }
                };

                const resolvePreferred = () => {
                    try {
                        const stored = window.localStorage.getItem(storageKey);
                        if (stored === 'light' || stored === 'dark') {
                            return stored;
                        }
                    } catch (error) {
                        /* no-op */
                    }

                    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
                        ? 'dark'
                        : 'light';
                };

                const applyTheme = (value, remember = true) => {
                    const root = document.documentElement;
                    root.classList.toggle('dark', value === 'dark');
                    if (remember) {
                        persist(value);
                    }
                    return value;
                };

                const syncPreferred = () => applyTheme(resolvePreferred(), false);

                syncPreferred();

                window.JoinTheme = {
                    toggle() {
                        const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                        applyTheme(next);
                        return next;
                    },
                    current() {
                        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                    },
                    sync: syncPreferred,
                };

                window.addEventListener('livewire:navigated', syncPreferred);
            })();
        </script>
    </head>
    <body class="bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-[-220px] z-0 mx-auto h-[420px] w-[720px] rounded-full blur-[140px]" style="background: radial-gradient(circle at 20% 20%, rgba(255,180,0,0.35), transparent), radial-gradient(circle at 80% 0%, rgba(255,64,129,0.3), transparent);"></div>
            <div class="pointer-events-none absolute inset-x-0 bottom-[-200px] z-0 mx-auto h-[360px] w-[620px] rounded-full blur-[140px]" style="background: radial-gradient(circle at 20% 80%, rgba(14,165,233,0.25), transparent), radial-gradient(circle at 80% 100%, rgba(99,102,241,0.3), transparent);"></div>

            <x-layouts.marketing-header :prefix="route('home')" :cta-href="route('login')" cta-label="Login" />

            <main class="relative z-10 mx-auto w-full max-w-5xl px-6 pb-16 pt-32 sm:pt-36">
                {{ $slot }}
            </main>
        </div>

        @livewireScriptConfig
        <script>
            (function () {
                const syncThemeLabel = () => {
                    var label = document.querySelector('[data-theme-label]');
                    var sun = document.querySelector('[data-theme-icon="sun"]');
                    var moon = document.querySelector('[data-theme-icon="moon"]');

                    if (!label || !sun || !moon) {
                        return;
                    }

                    var isDark = document.documentElement.classList.contains('dark');
                    label.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
                    sun.classList.toggle('hidden', !isDark);
                    moon.classList.toggle('hidden', isDark);
                };

                const bindThemeToggle = () => {
                    var toggle = document.querySelector('[data-theme-toggle]');
                    if (!toggle || toggle.dataset.themeBound === 'true') {
                        return;
                    }

                    toggle.dataset.themeBound = 'true';

                    toggle.addEventListener('click', function () {
                        if (window.JoinTheme) {
                            window.JoinTheme.toggle();
                        }

                        syncThemeLabel();
                    });
                };

                const hydrateControls = () => {
                    bindThemeToggle();
                    syncThemeLabel();
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', hydrateControls, { once: true });
                } else {
                    hydrateControls();
                }

                window.addEventListener('livewire:navigated', function () {
                    requestAnimationFrame(hydrateControls);
                });

                window.addEventListener('storage', syncThemeLabel);
            })();
        </script>
        @stack('scripts')
    </body>
</html>
