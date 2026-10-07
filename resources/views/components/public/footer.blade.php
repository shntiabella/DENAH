<footer class="relative overflow-hidden bg-denah-dark text-denah-cream-muted">
    {{-- Pola dots & glow dekoratif opacity rendah --}}
    <div class="pointer-events-none absolute inset-0 bg-denah-dots opacity-25"></div>
    <div class="pointer-events-none absolute -left-40 top-0 h-96 w-96 rounded-full bg-denah-terracotta/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-40 bottom-0 h-96 w-96 rounded-full bg-denah-terracotta-light/5 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-8 lg:flex-row lg:items-start">
            {{-- Brand (Kiri) --}}
            <div class="max-w-xs">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5">
                    <img src="{{ asset('img/logo.png') }}" alt="DENAH Logo" class="h-16 w-auto object-contain mix-blend-multiply">
                    <span class="leading-tight">
                        <span class="block font-display text-lg font-extrabold tracking-tight text-denah-cream">DENAH</span>
                        <span class="block text-[10px] font-medium tracking-wide text-denah-cream-muted/80">Digital Ecosystem &amp; Navigation for Area Housing</span>
                    </span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-denah-cream-muted/80">
                    {{ __('Platform pemetaan dan klasifikasi permukiman. Eksplorasi informasi spasial mengenai persebaran dan klasifikasi permukiman melalui peta interaktif.') }}
                </p>
                {{-- Sosial media (outline krem) --}}
                <div class="mt-6 flex items-center gap-2.5">
                    <a href="#" aria-label="Facebook"
                       class="grid h-9 w-9 place-items-center rounded-full border border-denah-cream/25 text-denah-cream/80 transition hover:border-denah-terracotta hover:text-denah-terracotta-light">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-7h2.4l.4-3h-2.8V9.1c0-.9.3-1.6 1.7-1.6h1.3V4.9c-.3 0-1.1-.1-2-.1-2 0-3.4 1.2-3.4 3.5V11H8.5v3h2.6v7h2.4z"/></svg>
                    </a>
                    <a href="#" aria-label="Instagram"
                       class="grid h-9 w-9 place-items-center rounded-full border border-denah-cream/25 text-denah-cream/80 transition hover:border-denah-terracotta hover:text-denah-terracotta-light">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.8"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="#" aria-label="X"
                       class="grid h-9 w-9 place-items-center rounded-full border border-denah-cream/25 text-denah-cream/80 transition hover:border-denah-terracotta hover:text-denah-terracotta-light">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h3.2l4.6 6.1L16.9 4H20l-6.7 7.9L20 20h-3.2l-5-6.6L6.9 20H4l7-8.2L4 4z"/></svg>
                    </a>
                    <a href="#" aria-label="YouTube"
                       class="grid h-9 w-9 place-items-center rounded-full border border-denah-cream/25 text-denah-cream/80 transition hover:border-denah-terracotta hover:text-denah-terracotta-light">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2.5" y="6" width="19" height="12" rx="3.5"/><path d="M10.5 9.8v4.4l4-2.2-4-2.2z" fill="currentColor" stroke="none"/></svg>
                    </a>
                </div>
            </div>
            {{-- Kolom Kanan: Navigasi, Peta & Data, Wilayah Kajian --}}
            <div class="flex flex-wrap gap-8 lg:gap-12 ml-auto">
                    {{-- Navigasi --}}
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-denah-cream">{{ __('Navigasi') }}</h4>
                        <ul class="space-y-2 text-sm">
                            @foreach ([
                                ['Beranda', 'home'],
                                ['Tentang', 'tentang'],
                                ['Metodologi', 'metodologi'],
                            ] as [$label, $route])
                                <li>
                                    <a href="{{ route($route) }}" wire:navigate
                                       class="relative inline-block text-denah-cream-muted transition after:absolute after:-bottom-0.5 after:left-0 after:h-px after:w-0 after:bg-denah-terracotta after:transition-all after:duration-300 hover:text-denah-terracotta-light hover:after:w-full">{{ __($label) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Peta & Data --}}
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-denah-cream">{{ __('Peta & Data') }}</h4>
                        <ul class="space-y-2 text-sm">
                            @foreach ([
                                ['Peta Interaktif', 'peta'],
                                ['Statistik', 'statistik'],
                            ] as [$label, $route])
                                <li>
                                    <a href="{{ route($route) }}" wire:navigate
                                       class="relative inline-block text-denah-cream-muted transition after:absolute after:-bottom-0.5 after:left-0 after:h-px after:w-0 after:bg-denah-terracotta after:transition-all after:duration-300 hover:text-denah-terracotta-light hover:after:w-full">{{ __($label) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Wilayah kajian --}}
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-denah-cream">{{ __('Wilayah Kajian') }}</h4>
                        <p class="max-w-[210px] text-xs leading-relaxed text-denah-cream-muted">
                            {{ __('Data geospasial permukiman hasil') }}<br>{{ __('pemetaan dan klasifikasi kawasan') }}
                        </p>
                        <p class="mt-3 font-mono text-xs tracking-[0.18em] text-denah-terracotta-light/80">7&#176;56'15"S | 112&#176;37'35"E</p>
                    </div>
            </div>
        </div>

        <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-6 text-xs text-denah-cream-muted/70 sm:flex-row">
            <p>&copy; {{ date('Y') }} DENAH. {{ __('All rights reserved.') }}</p>
            <p class="flex items-center gap-1.5">
                <svg class="h-3.5 w-3.5 text-denah-terracotta" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                {{ __('Data geospasial permukiman') }}
            </p>
        </div>
    </div>
</footer>
