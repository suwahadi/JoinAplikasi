<div class="relative isolate overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-[-180px] z-0 mx-auto h-[360px] w-[600px] rounded-full blur-[120px]" style="background: radial-gradient(circle at 30% 20%, rgba(255,180,0,0.3), transparent), radial-gradient(circle at 70% 0%, rgba(255,64,129,0.2), transparent);"></div>

    <x-layouts.marketing-header :prefix="route('home')" :authUser="auth()->user()" />

    <main class="relative z-10 pt-28 pb-24">
        <div class="mx-auto max-w-2xl space-y-6 px-6">

            {{-- Page Header --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:-translate-y-0.5 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M15 19l-7-7 7-7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Dashboard
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-orange-500">Pengaturan</p>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Profil Saya</h1>
                </div>
            </div>

            {{-- Update Profile Info --}}
            <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <livewire:profile.update-profile-information-form />
            </div>

            {{-- Update Password --}}
            <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <livewire:profile.update-password-form />
            </div>

            {{-- Delete Account --}}
            <div class="rounded-[28px] border border-red-200 bg-white p-6 shadow-sm dark:border-red-500/20 dark:bg-slate-900">
                <livewire:profile.delete-user-form />
            </div>

        </div>
    </main>
</div>
