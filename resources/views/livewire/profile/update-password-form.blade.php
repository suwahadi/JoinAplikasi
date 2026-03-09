<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <div class="mb-5 flex items-center gap-3">
        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
            <svg class="h-4 w-4 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="5" y="11" width="14" height="10" rx="2" stroke-width="1.5"/>
                <path d="M8 11V7a4 4 0 018 0v4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Ubah Password</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Gunakan password panjang dan unik untuk keamanan akun.</p>
        </div>
    </div>

    {{-- Success Banner --}}
    <div
        x-data="{ show: false }"
        x-show="show"
        x-init="$watch('show', v => { if(v) setTimeout(() => show = false, 3000) })"
        @password-updated.window="show = true"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mb-4 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
    >
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Password berhasil diperbarui.
    </div>

    <form wire:submit="updatePassword" class="space-y-4">
        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password Saat Ini</label>
            <input
                wire:model="current_password"
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-orange-400"
            />
            @error('current_password')
                <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                    <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 8v4m0 4h.01" stroke-width="1.5" stroke-linecap="round"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password Baru</label>
            <input
                wire:model="password"
                id="update_password_password"
                name="password"
                type="password"
                autocomplete="new-password"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-orange-400"
            />
            @error('password')
                <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                    <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 8v4m0 4h.01" stroke-width="1.5" stroke-linecap="round"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Konfirmasi Password Baru</label>
            <input
                wire:model="password_confirmation"
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-orange-400"
            />
            @error('password_confirmation')
                <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                    <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 8v4m0 4h.01" stroke-width="1.5" stroke-linecap="round"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="pt-1">
            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:opacity-60 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                <svg wire:loading wire:target="updatePassword" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Perbarui Password
            </button>
        </div>
    </form>
</section>
