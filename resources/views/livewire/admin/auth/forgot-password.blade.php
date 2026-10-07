<div class="w-full max-w-md">
    <div class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-md">
        <div class="mb-7 text-center">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-denah-terracotta/15 text-denah-terracotta-light">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </span>
            <h1 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-denah-cream">Lupa Password</h1>
            <p class="mt-1 text-sm text-denah-cream-muted">Kami akan mengirim tautan reset ke email Anda.</p>
        </div>

        @if ($sent)
            <div class="rounded-lg border border-green-500/30 bg-green-500/10 p-4 text-sm text-green-300">
                Tautan reset password telah dikirim ke <strong>{{ $email }}</strong>. Periksa email Anda.
            </div>
            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" wire:navigate class="text-sm font-medium text-denah-terracotta-light hover:text-denah-terracotta">&larr; Kembali ke login</a>
            </div>
        @else
            <form wire:submit="sendResetLink" class="space-y-5">
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-denah-cream">Email</label>
                    <input id="email" type="email" wire:model="email" wire:model.blur="email"
                           class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-2.5 text-sm text-denah-cream placeholder-denah-cream-muted/60 outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"
                           placeholder="admin@denah.id" autocomplete="email" />
                    @error('email') <p class="mt-1.5 text-xs font-medium text-red-400">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-denah-terracotta px-4 py-2.5 text-sm font-semibold text-denah-cream shadow-lg shadow-denah-terracotta/25 transition hover:bg-denah-terracotta-light hover:text-denah-dark">
                    <span wire:loading.remove wire:target="sendResetLink">Kirim Tautan Reset</span>
                    <svg class="h-4 w-4 animate-spin" wire:loading wire:target="sendResetLink" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                </button>
            </form>
            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" wire:navigate class="text-xs text-denah-cream-muted hover:text-denah-terracotta-light">&larr; Kembali ke login</a>
            </div>
        @endif
    </div>
</div>
