<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <div class="mb-5 flex items-center gap-3">
        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-50 dark:bg-orange-500/10">
            <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="8" r="4" stroke-width="1.5"/>
                <path d="M4 20c0-3.866 3.582-7 8-7s8 3.134 8 7" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </span>
        <div>
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Informasi Profil</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui nama dan alamat email akun kamu.</p>
        </div>
    </div>

    {{-- Success Banner --}}
    <div
        x-data="{ show: false }"
        x-show="show"
        x-init="$watch('show', v => { if(v) setTimeout(() => show = false, 3000) })"
        @profile-updated.window="show = true"
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
        Profil berhasil disimpan.
    </div>

    <form wire:submit="updateProfileInformation" class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap</label>
            <input
                wire:model="name"
                id="name"
                name="name"
                type="text"
                required
                autofocus
                autocomplete="name"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-orange-400"
            />
            @error('name')
                <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                    <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 8v4m0 4h.01" stroke-width="1.5" stroke-linecap="round"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Alamat Email</label>
            <input
                wire:model="email"
                id="email"
                name="email"
                type="email"
                required
                autocomplete="username"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-orange-400"
            />
            @error('email')
                <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                    <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path d="M12 8v4m0 4h.01" stroke-width="1.5" stroke-linecap="round"/></svg>
                    {{ $message }}
                </p>
            @enderror

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                    <p class="text-xs text-amber-700 dark:text-amber-300">
                        Email kamu belum diverifikasi.
                        <button wire:click.prevent="sendVerification" class="underline transition hover:text-amber-900 dark:hover:text-amber-100">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            Link verifikasi baru sudah dikirim ke email kamu.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="pt-1">
            <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-slate-800 disabled:opacity-60 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                <svg wire:loading wire:target="updateProfileInformation" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>
    </form>
</section>
