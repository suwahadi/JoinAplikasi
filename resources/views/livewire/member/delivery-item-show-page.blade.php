@extends('layouts.app')

@section('title', 'Detail Delivery')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-8">
        <a href="{{ route('member.deliveries') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>

        <h1 class="mt-2 text-2xl font-bold">Detail Delivery</h1>

        <div class="mt-6 space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Grup</div>
                        <div class="mt-1 text-slate-900 dark:text-slate-100">{{ $deliveryItem->delivery->group->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Item Produk</div>
                        <div class="mt-1 text-slate-900 dark:text-slate-100">{{ $deliveryItem->credential?->productItem?->name ?? '-' }}</div>
                    </div>
                </div>

                <div class="mt-2">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Pengguna</div>
                    <div class="mt-1 flex items-center gap-2">
                        <code class="rounded bg-slate-100 px-2 py-1 text-slate-800 dark:bg-slate-800 dark:text-slate-200">{{ $deliveryItem->credential?->username ?? '-' }}</code>
                        <button x-data x-on:click="navigator.clipboard.writeText(@js($deliveryItem->credential?->username ?? ''))" class="text-xs font-semibold text-indigo-600 hover:underline">Salin</button>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kata Sandi</div>
                    @php
                        $pwd = $deliveryItem->credential?->password ?? '';
                        $masked = str_repeat('•', max(4, strlen($pwd)));
                    @endphp
                    <div class="mt-1 flex items-center gap-2">
                        <code x-data="{show:false}" class="rounded bg-slate-100 px-2 py-1 text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                            <span x-show="!show">{{ $masked }}</span>
                            <span x-show="show">{{ $pwd }}</span>
                        </code>
                        <button x-data="{show:false}" x-on:click="show=!show" class="text-xs font-semibold text-indigo-600 hover:underline">Tampilkan</button>
                        <button x-data x-on:click="navigator.clipboard.writeText(@js($pwd))" class="text-xs font-semibold text-indigo-600 hover:underline">Salin</button>
                    </div>
                </div>

                @if ($deliveryItem->credential?->instructions_markdown)
                    <div class="prose mt-6 max-w-none dark:prose-invert">
                        {!! (new 
                            Parsedown())->text($deliveryItem->credential->instructions_markdown) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
