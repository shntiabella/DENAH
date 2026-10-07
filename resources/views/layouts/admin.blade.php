@php
    use App\Support\AdminNav;
    $nav = AdminNav::items();
    $current = request()->route()?->getName();
    $crumbs = $breadcrumbs ?? [];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'DENAH CMS') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Leaflet untuk preview peta di modul data spasial --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        @stack('head')
        {{-- Alpine store sidebar (mobile collapse) --}}
        <script>
            document.addEventListener('alpine:init', () => {
                window.Alpine.store('sidebar', { open: false });
            });
        </script>
    </head>
    <body class="h-full font-sans antialiased bg-denah-cream-bg text-denah-ink">
        <div class="min-h-full lg:flex">
            {{-- Sidebar --}}
            <aside x-data x-bind:class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed inset-y-0 left-0 z-40 w-64 transform border-r border-white/10 bg-denah-dark text-denah-cream transition-transform duration-300 lg:translate-x-0">
                <div class="flex h-full flex-col">
                    <div class="flex h-16 items-center gap-2.5 border-b border-white/10 px-5">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-denah-terracotta text-denah-cream">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6v14M15 3v14"/></svg>
                        </span>
                        <div class="leading-tight">
                            <div class="font-display text-base font-extrabold tracking-tight">DENAH</div>
                            <div class="text-[10px] tracking-wide text-denah-cream-muted">Admin Panel</div>
                        </div>
                    </div>
                    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                        @foreach ($nav as $item)
                            @php $isActive = $current === $item['route'] || (isset($item['match']) && in_array($current, $item['match'])); @endphp
                            <a href="{{ route($item['route']) }}" wire:navigate
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive ? 'bg-denah-terracotta text-denah-cream' : 'text-denah-cream-muted hover:bg-white/5 hover:text-denah-cream' }}">
                                <span class="h-5 w-5 shrink-0">{!! $item['icon'] !!}</span>
                                <span>{{ __($item['label']) }}</span>
                            </a>
                        @endforeach
                    </nav>
                    <div class="border-t border-white/10 px-4 py-3">
                        <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 text-xs text-denah-cream-muted hover:text-denah-terracotta-light">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Lihat Website
                        </a>
                    </div>
                </div>
            </aside>
            {{-- Overlay mobile --}}
            <div x-data x-show="$store.sidebar.open" x-cloak @click="$store.sidebar.open = false"
                 class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>
            {{-- Main area --}}
            <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
                {{-- Glow coklat hangat samar di kanan atas --}}
                <div class="pointer-events-none absolute right-0 top-0 h-72 w-72 rounded-full bg-denah-terracotta/10 blur-3xl" aria-hidden="true"></div>
                {{-- Topbar --}}
                <header class="sticky top-0 z-20 border-b border-denah-sand/60 bg-denah-paper/85 backdrop-blur-xl">
                    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                        <button type="button" x-data @click="$store.sidebar.open = ! $store.sidebar.open"
                                class="grid h-9 w-9 place-items-center rounded-lg border border-denah-sand/70 text-denah-ink-muted hover:bg-denah-cream-bg lg:hidden">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="min-w-0 flex-1">
                            <h1 class="truncate font-display text-lg font-bold text-denah-ink">{{ $pageTitle ?? '' }}</h1>
                        </div>
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

                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    {{-- Breadcrumb --}}
                    @if (! empty($crumbs))
                    <nav class="mb-4" aria-label="Breadcrumb">
                        <ol class="flex flex-wrap items-center gap-1.5 text-sm">
                            @foreach ($crumbs as $i => $crumb)
                                @if (! $loop->first)
                                    <li class="text-denah-sand"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></li>
                                @endif
                                <li>
                                    @if (! empty($crumb['url']) && ! ($loop->last))
                                        <a href="{{ $crumb['url'] }}" wire:navigate class="text-denah-ink-muted hover:text-denah-terracotta">{{ __($crumb['label']) }}</a>
                                    @else
                                        <span class="font-semibold text-denah-ink">{{ __($crumb['label']) }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>



        {{-- Toast container --}}
        @include('admin.partials.toast')

        @stack('scripts')
    </body>
</html>
