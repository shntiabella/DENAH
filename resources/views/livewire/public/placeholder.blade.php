<div>
    {{-- ============ HERO (gelap) ============ --}}
    <section class="relative overflow-hidden bg-denah-dark text-denah-cream">
        <div class="pointer-events-none absolute inset-0 bg-denah-dots opacity-25"></div>
        <div class="pointer-events-none absolute -left-40 bottom-0 h-80 w-80 rounded-full bg-denah-terracotta/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-40 top-0 h-80 w-80 rounded-full bg-denah-terracotta-light/5 blur-3xl"></div>

        <div class="relative mx-auto max-w-3xl px-4 py-28 text-center sm:px-6 sm:py-36 lg:px-8">
            <span class="inline-flex items-center gap-2.5 rounded-full border border-white/10 bg-black/40 px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-denah-terracotta-light backdrop-blur-md">
                <span class="h-1.5 w-1.5 rounded-full bg-denah-terracotta"></span>
                {{ __('Segera Hadir') }}
            </span>
            <h1 class="mt-6 font-display text-4xl font-extrabold tracking-tight text-denah-cream sm:text-5xl">
                {{ $title }}
            </h1>
            <p class="mx-auto mt-5 max-w-md text-base leading-relaxed text-denah-cream-muted">
                {{ __('Halaman ini sedang dalam pengembangan dan akan segera tersedia. Sementara itu, silakan jelajahi halaman lain yang sudah dapat diakses.') }}
            </p>
            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" wire:navigate
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-denah-terracotta px-6 py-3.5 text-sm font-semibold text-denah-cream shadow-lg shadow-black/30 transition-all duration-300 hover:scale-[1.03] hover:bg-denah-terracotta-light hover:text-denah-dark">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    {{ __('Kembali ke Beranda') }}
                </a>
                <a href="{{ route('peta') }}" wire:navigate
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-denah-cream/40 px-6 py-3.5 text-sm font-semibold text-denah-cream transition-all duration-300 hover:scale-[1.03] hover:border-denah-cream hover:bg-white/5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    {{ __('Jelajahi Peta') }}
                </a>
            </div>
        </div>
    </section>

    {{-- ============ YANG AKAN TERSEDIA (gelap) ============ --}}
    <section class="bg-denah-dark-alt py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-denah-terracotta">
                    <span class="h-px w-8 bg-denah-terracotta"></span>{{ __('Dalam Pengembangan') }}<span class="h-px w-8 bg-denah-terracotta"></span>
                </span>
                <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-denah-cream sm:text-4xl">
                    {{ __('Yang Akan Tersedia') }}
                </h2>
                <p class="mt-4 text-base text-denah-cream-muted">
                    {{ __('Berikut sejumlah fitur dan informasi yang akan kami hadirkan di halaman ini.') }}
                </p>
            </div>
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['icon' => 'map', 'title' => __('Peta Interaktif'), 'text' => __('Navigasi peta dengan legenda klasifikasi permukiman.')],
                    ['icon' => 'chart', 'title' => __('Statistik'), 'text' => __('Ringkasan tren persebaran permukiman dalam bentuk angka.')],
                    ['icon' => 'layers', 'title' => __('Metodologi'), 'text' => __('Penjelasan proses pemetaan dan klasifikasi permukiman secara transparan.')],
                ] as $feature)
                    <div class="group glass-dark rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1 hover:border-denah-terracotta/60 hover:shadow-warm">
                        <span class="grid h-12 w-12 place-items-center rounded-full border border-denah-terracotta/30 bg-denah-terracotta/10 p-3 text-denah-terracotta transition-colors duration-300 group-hover:border-denah-terracotta group-hover:bg-denah-terracotta group-hover:text-denah-dark">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                @if ($feature['icon'] === 'map')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6v14M15 3v14"/>
                                @elseif ($feature['icon'] === 'chart')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M6 16v-5M10 16V7M14 16v-8M18 16V5"/>
                                @elseif ($feature['icon'] === 'download')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5M5 21h14"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l9 5 9-5"/>
                                @endif
                            </svg>
                        </span>
                        <h3 class="mt-5 font-display text-base font-bold text-denah-cream">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-denah-cream-muted">{{ $feature['text'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-14 text-center">
                <a href="{{ route('home') }}" wire:navigate
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-denah-terracotta px-7 py-3.5 text-sm font-semibold text-denah-cream shadow-lg shadow-denah-terracotta/25 transition-all duration-300 hover:scale-[1.03] hover:bg-denah-terracotta-light hover:text-denah-dark">
                    {{ __('Kembali ke Beranda') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </section>
</div>
