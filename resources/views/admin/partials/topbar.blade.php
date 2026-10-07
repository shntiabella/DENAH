<header class="sticky top-0 z-20 border-b border-denah-sand/60 bg-denah-paper/85 backdrop-blur-xl">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        {{-- Mobile toggle --}}
        <button type="button"
                x-data
                @click="$store.sidebar.open = ! $store.sidebar.open"
                class="grid h-9 w-9 place-items-center rounded-lg border border-denah-sand/70 text-denah-ink-muted hover:bg-denah-cream-bg lg:hidden">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Page title slot --}}
        <div class="min-w-0 flex-1">
            <h1 class="truncate font-display text-lg font-bold text-denah-ink">{{ $pageTitle ?? '' }}</h1>
        </div>

        {{-- Admin user + logout --}}
        <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
                <div class="text-sm font-semibold text-denah-ink">{{ auth()->user()?->name }}</div>
                <div class="text-xs text-denah-ink-muted">{{ auth()->user()?->email }}</div>
            </div>
            <span class="grid h-9 w-9 place-items-center rounded-full bg-denah-terracotta/15 font-semibold text-denah-terracotta">
                {{ strtoupper(auth()->user()?->initials() ?? 'AD') }}
            </span>
            <button type="button" wire:click="logout"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-denah-sand/70 px-3 py-2 text-sm font-medium text-denah-ink-muted transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="hidden sm:inline">Keluar</span>
            </button>
        </div>
    </div>
</header>
