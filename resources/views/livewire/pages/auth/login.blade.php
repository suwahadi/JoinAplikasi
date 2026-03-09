<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<section class="grid gap-10 rounded-[32px] border border-white/50 bg-white/80 p-8 shadow-[0_30px_80px_rgba(15,23,42,0.15)] backdrop-blur dark:border-slate-800 dark:bg-slate-900/70 lg:grid-cols-[1.1fr_0.9fr]">
    <div class="space-y-6">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-500">Masuk akun</p>
        <h1 class="text-3xl font-semibold text-slate-900 sm:text-4xl dark:text-white">Tetap lanjutkan kolaborasi patungan kamu</h1>
        <p class="text-base text-slate-600 dark:text-slate-300">Akses dasbor, pantau progress kursi, dan selesaikan pembayaran Midtrans hanya dengan sekali masuk. Tema gelap/terang mengikuti preferensi kamu.</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Pembayaran aman</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Midtrans + verifikasi signature otomatis.</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Notifikasi realtime</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Info seat & reminder tagihan langsung ke dashboard.</p>
            </div>
        </div>

        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
            <li class="flex items-center gap-3">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-300">1</span>
                Pantau status grup favoritmu dalam satu dasbor terpadu.
            </li>
            <li class="flex items-center gap-3">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-500/15 text-sky-600 dark:text-sky-300">2</span>
                Kelola pembayaran dan arsip invoice tanpa rumit.
            </li>
            <li class="flex items-center gap-3">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-500/15 text-rose-600 dark:text-rose-300">3</span>
                Dapatkan dukungan admin melalui WhatsApp kapan pun.
            </li>
        </ul>
    </div>

    <div class="rounded-[28px] border border-slate-100 bg-white/90 p-8 shadow-xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900/80">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Masuk sekarang</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">Patungin.id Dashboard</h2>
            </div>
            <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">Realtime</span>
        </div>

        @if (session('status'))
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-emerald-200/60 bg-emerald-50/80 px-4 py-3 text-emerald-700 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M5 13l4 4L19 7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <div>
                    <p class="text-sm font-semibold">Berhasil</p>
                    <p class="text-sm">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        @php
            $authError = session('error') ?? ($errors->any() ? $errors->first() : null);
        @endphp

        @if ($authError)
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-rose-200/60 bg-rose-50/80 px-4 py-3 text-rose-700 shadow-sm dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200" role="alert">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 9v4m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <div>
                    <p class="text-sm font-semibold">Gagal masuk</p>
                    <p class="text-sm">{{ $authError }}</p>
                </div>
            </div>
        @endif

        <form wire:submit="login" class="mt-6 space-y-5">
            <div>
                <label for="email" class="text-sm font-medium text-slate-600 dark:text-slate-200">Email</label>
                <input
                    wire:model="form.email"
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="username"
                    required
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-700"
                />
                @error('form.email')
                    <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="text-sm font-medium text-slate-600 dark:text-slate-200">Password</label>
                <input
                    wire:model="form.password"
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-700"
                />
                @error('form.password')
                    <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label for="remember" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300">
                    <input
                        wire:model="form.remember"
                        id="remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-600 dark:border-slate-600 dark:bg-slate-900"
                    />
                    Ingat saya
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-orange-600 transition hover:text-orange-500 dark:text-orange-300" wire:navigate>
                        Lupa password?
                    </a>
                @endif
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 dark:bg-white dark:text-slate-900">
                Masuk Sekarang
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-300">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-orange-600 hover:text-orange-500 dark:text-orange-300" wire:navigate>
                Daftar gratis
            </a>
        </p>
        <p class="mt-2 text-center text-xs text-slate-400 dark:text-slate-500">Support admin aktif setiap hari jam 08.00 - 22.00 WIB.</p>
    </div>
</section>
