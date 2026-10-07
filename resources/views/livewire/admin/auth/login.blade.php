<div class="w-full max-w-md">
    <div class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-md">
        <div class="mb-7 flex flex-col items-center text-center">
            <span class="grid h-12 w-12 place-items-center rounded-xl bg-denah-terracotta text-denah-cream shadow-lg shadow-denah-terracotta/30">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6v14M15 3v14"/></svg>
            </span>
            <h1 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-denah-cream">DENAH CMS</h1>
            <p class="mt-1 text-sm text-denah-cream-muted">Masuk untuk mengelola konten website</p>
        </div>

        <form wire:submit="login" class="space-y-5">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-denah-cream">Email</label>
                <input id="email" type="email" wire:model="email" wire:model.blur="email"
                       class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-2.5 text-sm text-denah-cream placeholder-denah-cream-muted/60 outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"
                       placeholder="admin@denah.id" autocomplete="email" />
                @error('email') <p class="mt-1.5 text-xs font-medium text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-denah-cream">Password</label>
                <div class="relative">
                    <input id="password" type="password" wire:model="password" wire:model.blur="password"
                           class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-2.5 pr-10 text-sm text-denah-cream placeholder-denah-cream-muted/60 outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"
                           placeholder="••••••••" autocomplete="current-password" />
                </div>
                @error('password') <p class="mt-1.5 text-xs font-medium text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-denah-cream-muted">
                    <input type="checkbox" wire:model="remember" class="rounded border-white/20 bg-black/30 text-denah-terracotta focus:ring-denah-terracotta" />
                    Ingat saya
                </label>
                <a href="{{ route('admin.password.request') }}" wire:navigate class="font-medium text-denah-terracotta-light hover:text-denah-terracotta">Lupa password?</a>
            </div>

            <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-denah-terracotta px-4 py-2.5 text-sm font-semibold text-denah-cream shadow-lg shadow-denah-terracotta/25 transition hover:bg-denah-terracotta-light hover:text-denah-dark">
                <svg class="h-4 w-4" wire:loading.delay.remove wire:target="login" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <svg class="h-4 w-4 animate-spin" wire:loading wire:target="login" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                Masuk
            </button>
        </form>

        <p class="mt-6 text-center text-xs text-denah-cream-muted">
            Akun admin dibuat oleh pengelola. Pendaftaran tidak tersedia.
        </p>
    </div>

    <div class="mt-5 text-center">
        <a href="{{ route('home') }}" wire:navigate class="text-xs text-denah-cream-muted hover:text-denah-terracotta-light">&larr; Kembali ke website</a>
    </div>
</div>
