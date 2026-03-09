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
        <div class="min-h-screen">{{ $slot }}</div>

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
