<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section x-data="{ open: false }">
    <div class="mb-5 flex items-center gap-3">
        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 dark:bg-red-500/10">
            <svg class="h-4 w-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Hapus Akun</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Tindakan ini bersifat permanen dan tidak dapat dibatalkan.</p>
        </div>
    </div>

    <p class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
        Setelah akun dihapus, semua data dan riwayat transaksi akan hilang selamanya. Pastikan kamu sudah mengunduh semua data penting sebelum melanjutkan.
    </p>

    <button @click="open = true" type="button" class="inline-flex items-center gap-2 rounded-2xl border border-red-300 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:-translate-y-0.5 hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <polyline points="3 6 5 6 21 6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M10 11v6M14 11v6M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Hapus Akun Saya
    </button>

    {{-- Delete Confirmation Modal --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-md rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="mb-4 flex items-center gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 dark:bg-red-500/20">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white">Hapus Akun?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>

            <p class="mb-5 text-sm text-slate-600 dark:text-slate-400">
                Semua data akun, riwayat pesanan, dan informasi terkait akan dihapus secara permanen. Masukkan password kamu untuk konfirmasi.
            </p>

            <form wire:submit="deleteUser">
                <div>
                    <label for="delete_password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                    <input
                        wire:model="password"
                        id="delete_password"
                        name="password"
                        type="password"
                        placeholder="Masukkan password kamu"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-red-400"
                    />
                    @error('password')
                        <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                            <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 8v4m0 4h.01" stroke-width="1.5" stroke-linecap="round"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-2xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600 disabled:opacity-60">
                        <svg wire:loading wire:target="deleteUser" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        Ya, Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
