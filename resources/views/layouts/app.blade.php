<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
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
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @livewireScriptConfig
    </body>
</html>
