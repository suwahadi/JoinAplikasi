@props([
    'columns' => [],
])

<footer class="border-t border-slate-200 bg-white/70 dark:border-slate-800 dark:bg-slate-900/40">
    <div class="mx-auto max-w-6xl px-6 py-14">
        <div class="grid gap-10 lg:grid-cols-4">
            <div class="space-y-4">
                <a href="/" class="inline-flex items-center gap-2 text-xl font-semibold text-slate-900 dark:text-white">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-pink-500 text-white">P</span>
                    Patungin.id
                </a>
                <p class="text-sm text-slate-500 dark:text-slate-400">Platform patungan premium yang aman, transparan, dan terkurasi untuk tim kreator Indonesia.</p>
            </div>

            @foreach($columns as $column)
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-900 dark:text-white">{{ $column['title'] }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-slate-500 dark:text-slate-400">
                        @foreach($column['items'] as $item)
                            <li>
                                <a href="{{ $item['href'] ?? '#' }}" class="transition hover:text-orange-500 dark:hover:text-orange-300">{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-6 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400 md:flex-row md:items-center md:justify-between">
            <p>© {{ now()->year }} Patungin Aplikasi. All rights reserved.</p>
            <div class="flex flex-wrap gap-4">
                <a href="#" class="hover:text-orange-500">Syarat & Ketentuan</a>
                <a href="#" class="hover:text-orange-500">Kebijakan Privasi</a>
                <a href="#" class="hover:text-orange-500">FAQ</a>
            </div>
        </div>
    </div>
</footer>
