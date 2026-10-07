@php
    $current = request()->route()?->getName();

    $navItems = [
        ['label' => __('Beranda'), 'route' => 'home'],
        ['label' => __('Peta'), 'route' => 'peta'],
        ['label' => __('Statistik'), 'route' => 'statistik'],
        ['label' => __('Tentang'), 'route' => 'tentang'],
        ['label' => __('Metodologi'), 'route' => 'metodologi'],
    ];
@endphp

<header x-data="{ scrolled: false }"
        @scroll.window.passive="scrolled = window.scrollY > 10"
        :class="scrolled ? 'bg-denah-dark-alt shadow-lg' : 'bg-denah-dark'"
        class="sticky top-0 z-50 w-full border-b border-white/10 transition-all duration-300">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">
            {{-- Logo + Brand --}}
            <a href="/" class="flex shrink-0 items-center gap-3">
                <img src="{{ asset('img/logo.png') }}" alt="DENAH Logo" class="h-10 w-10 rounded-full object-cover">
                <span class="block min-w-0">
                    <span class="block text-base font-bold tracking-wide text-white">DENAH</span>
                    <span class="block text-[10px] font-medium leading-tight text-gray-300">{{ __('Digital Ecosystem & Navigation for Area Housing') }}</span>
                </span>
            </a>

            {{-- Desktop navigation --}}
            <nav class="hidden items-center lg:flex">
                <ul class="flex items-center gap-7">
                    @foreach ($navItems as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="text-sm font-medium transition {{ $current === $item['route'] ? 'text-white' : 'text-denah-cream-muted hover:text-denah-terracotta-light' }}">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Kanan: language switcher + hamburger --}}
            <div class="flex items-center gap-2">
                {{-- Language switcher --}}
                <div class="hidden items-center rounded-full bg-denah-cream-bg p-0.5 text-xs font-semibold sm:flex">
                    <a href="{{ route('locale.switch', 'id') }}"
                       class="rounded-full px-2.5 py-1 transition {{ app()->getLocale() === 'id' ? 'bg-denah-terracotta text-white' : 'text-denah-ink-muted hover:text-denah-ink' }}">ID</a>
                    <a href="{{ route('locale.switch', 'en') }}"
                       class="rounded-full px-2.5 py-1 transition {{ app()->getLocale() === 'en' ? 'bg-denah-terracotta text-white' : 'text-denah-ink-muted hover:text-denah-ink' }}">EN</a>
                </div>

                {{-- Hamburger --}}
                <div x-data="{ open: false }">
                    <button type="button"
                            @click="open = ! open"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full text-denah-cream-muted transition hover:bg-white/10 hover:text-denah-cream lg:hidden"
                            aria-label="{{ __('Menu') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" x-show="!open" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" x-show="open" x-cloak />
                        </svg>
                    </button>

                    {{-- Mobile menu --}}
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="absolute inset-x-0 top-full border-b border-white/10 bg-denah-dark shadow-lg lg:hidden">
                        <div class="mx-auto max-w-7xl space-y-1 px-4 py-4 sm:px-6">
                            @foreach ($navItems as $item)
                                <a href="{{ route($item['route']) }}" @click="open = false"
                                   class="block rounded-lg px-3 py-2 text-sm font-medium {{ $current === $item['route'] ? 'bg-denah-terracotta/15 font-semibold text-denah-terracotta-light' : 'text-denah-cream-muted hover:bg-white/10 hover:text-denah-cream' }}">
                                    {{ $item['label'] }}
                                </a>
                            @endforeach

                            <div class="flex items-center gap-2 pt-3">
                                <div class="flex items-center rounded-full bg-denah-cream-bg p-0.5 text-xs font-semibold">
                                    <a href="{{ route('locale.switch', 'id') }}"
                                       class="rounded-full px-2.5 py-1 transition {{ app()->getLocale() === 'id' ? 'bg-denah-terracotta text-white' : 'text-denah-ink-muted' }}">ID</a>
                                    <a href="{{ route('locale.switch', 'en') }}"
                                       class="rounded-full px-2.5 py-1 transition {{ app()->getLocale() === 'en' ? 'bg-denah-terracotta text-white' : 'text-denah-ink-muted' }}">EN</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
