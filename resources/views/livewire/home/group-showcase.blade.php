<section id="grup-terakhir" class="mx-auto mt-24 max-w-6xl px-6" wire:init="load">
    <x-home.section-heading
        eyebrow="List grup terakhir aktif"
        title="Grup premium siap kamu ikuti"
        subtitle="Banyak grup sudah aktif dalam beberapa jam terakhir. Pilih layanan favorit dan amankan slotmu."
        align="left"
    />

    <div class="mt-10" data-tabs-scroll>
        <div class="relative flex items-center gap-4">
            <button type="button" data-scroll-prev class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow transition hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M15 19l-7-7 7-7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>

            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 w-12 bg-gradient-to-r from-white dark:from-slate-950 pointer-events-none"></div>
                <div class="absolute inset-y-0 right-0 w-12 bg-gradient-to-l from-white dark:from-slate-950 pointer-events-none"></div>

                <div class="flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth px-1 py-2" data-tabs-track>
                    @forelse($services as $service)
                        @php($isActive = $selectedServiceId === $service['id'])
                        <button
                            type="button"
                            wire:click="selectService({{ $service['id'] }})"
                            wire:loading.attr="disabled"
                            class="group inline-flex min-w-[180px] snap-start items-center gap-3 rounded-3xl border px-4 py-3 text-left transition hover:-translate-y-0.5 {{ $isActive ? 'border-transparent bg-white text-slate-900 shadow-xl shadow-slate-900/10 dark:bg-slate-900 dark:text-white' : 'border-slate-200 bg-white/70 text-slate-600 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-300' }}"
                        >
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-slate-900 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">
                                {{ mb_strtoupper(mb_substr($service['name'], 0, 2)) }}
                            </span>
                            <span class="flex-1">
                                <span class="block text-sm font-semibold">{{ $service['name'] }}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $service['group_count'] }} grup</span>
                            </span>
                        </button>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-white/60 px-4 py-3 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
                            Belum ada layanan aktif.
                        </div>
                    @endforelse
                </div>
            </div>

            <button type="button" data-scroll-next class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow transition hover:-translate-y-0.5 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 5l7 7-7 7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="mt-12 -mx-6 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth px-6 pb-4 lg:mx-0 lg:grid lg:grid-cols-2 lg:overflow-visible lg:px-0 lg:pb-0">
        @if(! $ready)
            @foreach(range(1, 4) as $i)
                <div class="w-[85vw] shrink-0 animate-pulse rounded-3xl border border-slate-200 bg-white/60 p-6 sm:w-[400px] lg:w-auto dark:border-slate-800 dark:bg-slate-900/50">
                    <div class="h-6 w-1/3 rounded bg-slate-200 dark:bg-slate-800"></div>
                    <div class="mt-4 h-4 w-1/4 rounded bg-slate-200 dark:bg-slate-800"></div>
                    <div class="mt-6 h-40 rounded-2xl bg-slate-100 dark:bg-slate-800/80"></div>
                </div>
            @endforeach
        @else
            @forelse($groups as $group)
                <div class="w-[85vw] shrink-0 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg shadow-slate-900/5 transition hover:-translate-y-0.5 sm:w-[400px] lg:w-auto dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $group['service'] }}</p>
                            <h3 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $group['name'] }}</h3>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $group['status_class'] }}">{{ $group['status_label'] }}</span>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm font-medium text-slate-700 dark:text-slate-200">
                            <p>{{ $group['filled'] }} / {{ $group['max_users'] }} slot terisi</p>
                            <span>{{ $group['percentage'] }}%</span>
                        </div>
                        <div class="mt-3 h-2 rounded-full bg-slate-100 dark:bg-slate-800">
                            <span class="block h-full rounded-full bg-gradient-to-r from-orange-500 to-pink-500" style="width: {{ $group['percentage'] }}%"></span>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-100 bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                            <span>List anggota</span>
                            <span>Live update</span>
                        </div>
                        <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                            @forelse($group['members'] as $index => $member)
                                <li class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-200">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-200">{{ $index + 1 }}</span>
                                    <span>{{ $member }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500 dark:text-slate-400">Belum ada anggota aktif.</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="mt-6 flex flex-col gap-4 text-sm text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">Mulai dari Rp {{ number_format($group['price'] ?? 0, 0, ',', '.') }}</p>
                            <p>Durasi {{ $group['duration'] }} hari</p>
                        </div>
                        @if($group['product_slug'])
                            <a href="{{ route('products.show', $group['product_slug']) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2 font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 dark:bg-white dark:text-slate-900">
                                Gabung grup
                                <svg class="ml-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M9 5l7 7-7 7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-200 p-10 text-center text-slate-500 dark:border-slate-700 dark:text-slate-300">
                    Belum ada aktivitas grup terbaru untuk layanan ini.
                </div>
            @endforelse
        @endif
    </div>

    <div class="mt-10 text-center text-sm text-slate-500 dark:text-slate-400" wire:loading.flex>
        Memuat list grup terbaru...
    </div>
</section>

@once
    @push('scripts')
        <script>
            (function () {
                function initTabScrollers() {
                    document.querySelectorAll('[data-tabs-scroll]').forEach(function (wrapper) {
                        if (wrapper.dataset.scrollBound === 'true') {
                            return;
                        }

                        var track = wrapper.querySelector('[data-tabs-track]');
                        var prev = wrapper.querySelector('[data-scroll-prev]');
                        var next = wrapper.querySelector('[data-scroll-next]');

                        if (!track || !prev || !next) {
                            return;
                        }

                        wrapper.dataset.scrollBound = 'true';
                        var update = function () {
                            var maxScroll = track.scrollWidth - track.clientWidth - 4;
                            var hasOverflow = maxScroll > 0;

                            prev.classList.toggle('hidden', !hasOverflow);
                            next.classList.toggle('hidden', !hasOverflow);

                            prev.disabled = !hasOverflow || track.scrollLeft <= 0;
                            next.disabled = !hasOverflow || track.scrollLeft >= maxScroll;
                        };

                        prev.addEventListener('click', function () {
                            track.scrollBy({ left: -220, behavior: 'smooth' });
                        });

                        next.addEventListener('click', function () {
                            track.scrollBy({ left: 220, behavior: 'smooth' });
                        });

                        track.addEventListener('scroll', update, { passive: true });
                        window.addEventListener('resize', update);
                        update();
                    });
                }

                document.addEventListener('DOMContentLoaded', initTabScrollers);
                document.addEventListener('livewire:load', initTabScrollers);
                document.addEventListener('livewire:navigated', initTabScrollers);
            })();
        </script>
    @endpush
@endonce
