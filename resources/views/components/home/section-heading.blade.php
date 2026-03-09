@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'align' => 'center',
])

@php($alignment = $align === 'left' ? 'text-left' : 'text-center')

<div {{ $attributes->class("{$alignment} space-y-3") }}>
    @if($eyebrow)
        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-500 dark:text-amber-300">{{ $eyebrow }}</p>
    @endif

    <h2 class="text-3xl font-semibold text-slate-900 dark:text-white sm:text-4xl">{{ $title }}</h2>

    @if($subtitle)
        <p class="text-base text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
    @endif
</div>
