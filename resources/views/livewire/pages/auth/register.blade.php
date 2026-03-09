<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<section class="grid gap-10 rounded-[32px] border border-white/50 bg-white/80 p-8 shadow-[0_30px_80px_rgba(15,23,42,0.15)] backdrop-blur dark:border-slate-800 dark:bg-slate-900/70 lg:grid-cols-[1.1fr_0.9fr]">
    <div class="space-y-6">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-orange-500">Daftar akun</p>
        <h1 class="text-3xl font-semibold text-slate-900 sm:text-4xl dark:text-white">Bangun grup langganan favoritmu bareng komunitas</h1>
        <p class="text-base text-slate-600 dark:text-slate-300">Buat akun baru dan mulai ajak teman bergabung ke slot layanan digital pilihanmu. Semua tampilan, warna, dan elemen UI konsisten dengan halaman utama.</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Slot fleksibel</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Atur jumlah kursi dan harga transparan per layanan.</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white/70 p-4 dark:border-slate-800 dark:bg-slate-900/60">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">Reminder otomatis</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pengingat tagihan & aktivasi seat diatur admin.</p>
            </div>
        </div>

        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
            <li class="flex items-center gap-3">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-500/15 text-indigo-600 dark:text-indigo-300">1</span>
                Registrasi gratis dengan email dan nama lengkap.
            </li>
            <li class="flex items-center gap-3">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-300">2</span>
                Pilih layanan, buka grup baru, atau gabung grup publik.
            </li>
            <li class="flex items-center gap-3">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-300">3</span>
                Proses Midtrans otomatis siap membantu patunganmu.
            </li>
        </ul>
    </div>

    <div class="rounded-[28px] border border-slate-100 bg-white/90 p-8 shadow-xl shadow-slate-900/10 dark:border-slate-800 dark:bg-slate-900/80">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">Mulai gratis</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-900 dark:text-white">Registrasi akun baru</h2>
            </div>
            <span class="rounded-full bg-orange-500/15 px-3 py-1 text-xs font-semibold text-orange-600 dark:bg-orange-500/10 dark:text-orange-200">2 menit</span>
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
            $registerError = session('error') ?? ($errors->any() ? $errors->first() : null);
        @endphp

        @if ($registerError)
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-rose-200/60 bg-rose-50/80 px-4 py-3 text-rose-700 shadow-sm dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200" role="alert">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 9v4m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <div>
                    <p class="text-sm font-semibold">Gagal mendaftar</p>
                    <p class="text-sm">{{ $registerError }}</p>
                </div>
            </div>
        @endif

        <form wire:submit="register" class="mt-6 space-y-5">
            <div>
                <label for="name" class="text-sm font-medium text-slate-600 dark:text-slate-200">Nama lengkap</label>
                <input
                    wire:model="name"
                    id="name"
                    type="text"
                    name="name"
                    autocomplete="name"
                    required
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-700"
                />
                @error('name')
                    <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="text-sm font-medium text-slate-600 dark:text-slate-200">Email</label>
                <input
                    wire:model="email"
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="username"
                    required
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-700"
                />
                @error('email')
                    <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="text-sm font-medium text-slate-600 dark:text-slate-200">Password</label>
                <input
                    wire:model="password"
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    required
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-700"
                />
                @error('password')
                    <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="text-sm font-medium text-slate-600 dark:text-slate-200">Konfirmasi password</label>
                <input
                    wire:model="password_confirmation"
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    required
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-slate-400 focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-900/70 dark:text-white dark:focus:border-slate-500 dark:focus:ring-slate-700"
                />
                @error('password_confirmation')
                    <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 dark:bg-white dark:text-slate-900">
                Daftar &amp; Mulai Patungan
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-300">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-orange-600 hover:text-orange-500 dark:text-orange-300" wire:navigate>
                Masuk di sini
            </a>
        </p>
        <p class="mt-2 text-center text-xs text-slate-400 dark:text-slate-500">Dengan mendaftar kamu menyetujui S&amp;K dan kebijakan privasi {{ config('app.name') }}.</p>
    </div>
</section>
