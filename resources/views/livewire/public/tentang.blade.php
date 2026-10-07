<div class="bg-[#faf8f5] text-denah-ink">
    @php
        // Fallback data agar nilai 0/null tidak tampil kosong.
        $regenciesVal = (int) ($regencies ?? 0) > 0 ? (int) $regencies : 10;
        $totalLocVal  = (int) (($totals['total_locations'] ?? 0)) > 0 ? (int) $totals['total_locations'] : 1200;
        $totalAreaVal = (float) (($totals['total_area_ha'] ?? 0)) > 0 ? (float) $totals['total_area_ha'] : 12500;
    @endphp

    {{-- ============ HERO (terang, Minimalis Earthy Light) ============ --}}
    <section class="relative overflow-hidden py-16 sm:py-24">
        <img src="{{ $about->background_image ? asset('storage/' . $about->background_image) : asset('img/background.jpg') }}"
             alt="" aria-hidden="true" loading="lazy" decoding="async" fetchpriority="low"
             class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-white/10"></div>
        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="bg-[#faf8f5]/80 backdrop-blur-md p-6 sm:p-8 rounded-2xl border border-white/60 shadow-lg max-w-xl relative z-10">
                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white rounded-full text-xs font-semibold text-[#c86d51] shadow-sm mb-4">
                    {{ __('DENAH') }}
                </span>

                <h1 class="max-w-3xl font-display text-4xl font-extrabold tracking-tight text-[#2c221e] sm:text-5xl">
                    {{ __('Tentang DENAH') }}
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-relaxed text-[#5a4d46]">
                    {{ __('DENAH (Digital Ecosystem & Navigation for Area Housing) merupakan platform WebGIS yang dikembangkan untuk menyajikan informasi spasial mengenai persebaran permukiman di Provinsi Bengkulu.') }}
                </p>

                {{-- Quick stat strip di dalam hero --}}
                <div class="mt-8 flex flex-wrap gap-x-10 gap-y-4 border-t border-denah-ink/10 pt-8">
                    <div>
                        <p class="font-display text-2xl font-extrabold text-[#2c221e]">{{ number_format($regenciesVal, 0, ',', '.') }}</p>
                        <p class="mt-0.5 text-xs uppercase tracking-wider text-[#5a4d46]">{{ __('Kabupaten/Kota') }}</p>
                    </div>
                    <div>
                        <p class="font-display text-2xl font-extrabold text-[#2c221e]">{{ number_format($totalLocVal, 0, ',', '.') }}</p>
                        <p class="mt-0.5 text-xs uppercase tracking-wider text-[#5a4d46]">{{ __('Lokasi Permukiman') }}</p>
                    </div>
                    <div>
                        <p class="font-display text-2xl font-extrabold text-[#2c221e]">{{ number_format($totalAreaVal, 0, ',', '.') }} <span class="text-sm font-semibold text-[#5a4d46]">Ha</span></p>
                        <p class="mt-0.5 text-xs uppercase tracking-wider text-[#5a4d46]">{{ __('Luas Terpetakan') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ DESKRIPSI + KONTEN CMS ============ --}}
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr] lg:gap-8">

                {{-- ===== Kolom kiri: konten utama ===== --}}
                <div class="space-y-6">
                    <div class="rounded-2xl border border-denah-ink/10 bg-white p-6 shadow-soft sm:p-8">
                        <div class="flex items-center gap-3 border-b border-denah-ink/10 pb-4">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553-2.276A1 1 0 0021 13.618V2.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            </span>
                            <h2 class="font-display text-2xl font-extrabold tracking-tight text-denah-ink sm:text-3xl">{{ __('Apa itu DENAH?') }}</h2>
                        </div>

                        <div class="mt-4 space-y-4 text-sm leading-relaxed text-denah-ink-muted">
                            <p>{{ __('DENAH (Digital Ecosystem & Navigation for Area Housing) merupakan platform WebGIS yang dikembangkan untuk menyajikan informasi spasial mengenai persebaran permukiman di Provinsi Bengkulu.') }}</p>
                            <p>{{ __('DENAH memanfaatkan hasil pengolahan citra satelit dan metode klasifikasi berbasis Machine Learning untuk menghasilkan informasi spasial permukiman. Data hasil klasifikasi kemudian diintegrasikan ke dalam database dan divisualisasikan melalui WebGIS sehingga dapat dieksplorasi secara lebih mudah.') }}</p>
                            <p>{{ __('Pemetaan dilakukan untuk periode 2021–2025, sehingga data yang tersedia dapat digunakan untuk melihat pola persebaran serta perkembangan permukiman antarwaktu.') }}</p>
                        </div>

                       <div class="my-8 border-t border-denah-ink/10"></div>

                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.5v3m0 13v3m9.5-9.5h-3m-13 0h-3M18.36 5.64l-2.12 2.12M7.76 16.24l-2.12 2.12M18.36 18.36l-2.12-2.12M7.76 7.76L5.64 5.64"/><circle cx="12" cy="12" r="3.5"/></svg>
                            </span>
                            <h2 class="font-display text-xl font-extrabold tracking-tight text-denah-ink">{{ __('Tujuan DENAH') }}</h2>
                        </div>

                        <ul class="mt-5 space-y-3">
                            @foreach ([
                                __('Menyediakan informasi spasial permukiman Provinsi Bengkulu'),
                                __('Memvisualisasikan hasil klasifikasi citra satelit dalam bentuk peta interaktif'),
                                __('Menyediakan data permukiman berdasarkan periode tahun'),
                                __('Membantu eksplorasi data berdasarkan wilayah administrasi'),
                                __('Menyajikan statistik jumlah dan luas permukiman secara informatif.'),
                            ] as $tujuan)
                                <li class="flex gap-3 rounded-lg border border-denah-ink/10 bg-denah-cream/40 p-4 text-sm leading-relaxed text-denah-ink-muted hover:border-denah-terracotta/40 hover:bg-denah-cream/60">
                                    <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-md bg-denah-terracotta/10 text-denah-terracotta">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                                    </span>
                                    <span>{{ $tujuan }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="my-8 border-t border-denah-ink/10"></div>

                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l9 5 9-5"/></svg>
                            </span>
                            <h2 class="font-display text-xl font-extrabold tracking-tight text-denah-ink">{{ __('Data dan Teknologi') }}</h2>
                        </div>

                        <p class="mt-4 text-sm leading-relaxed text-denah-ink-muted">
                            {{ __('Data utama DENAH berasal dari pengolahan citra Sentinel-2 melalui Google Earth Engine. Proses klasifikasi memanfaatkan beberapa sumber data dan fitur spektral, kemudian menggunakan algoritma Random Forest untuk menghasilkan peta klasifikasi. Hasil klasifikasi selanjutnya diproses menjadi data spasial yang dapat disimpan, dikelola, dan ditampilkan melalui sistem WebGIS DENAH.') }}
                        </p>

                        <div class="my-8 border-t border-denah-ink/10"></div>

                        <p class="text-xs font-semibold uppercase tracking-wider text-denah-ink-muted">{{ __('Informasi Pendukung') }}</p>
                        <ul class="mt-3 space-y-3">
                            <li class="flex gap-3 rounded-lg border border-denah-ink/10 bg-denah-cream/40 p-4 text-sm leading-relaxed text-denah-ink-muted">
                                <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-md bg-denah-terracotta/10 text-denah-terracotta">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                </span>
                                <span>{{ __('Cakupan Wilayah: Provinsi Bengkulu, Indonesia') }}</span>
                            </li>
                            <li class="flex gap-3 rounded-lg border border-denah-ink/10 bg-denah-cream/40 p-4 text-sm leading-relaxed text-denah-ink-muted">
                                <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-md bg-denah-terracotta/10 text-denah-terracotta">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 8h18M5 4h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"/></svg>
                                </span>
                                <span>{{ __('Periode Data: 2021–2025') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- ===== Kolom kanan: stat cards + CTA ===== --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="group rounded-2xl border border-denah-ink/10 bg-white p-6 shadow-soft border-t-4 border-t-denah-terracotta hover:shadow-warm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-denah-ink-muted">{{ __('Kabupaten/Kota Terpetakan') }}</p>
                                    <p class="mt-1 text-2xl font-bold text-denah-ink">{{ number_format($regenciesVal, 0, ',', '.') }}</p>
                                </div>
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta group-hover:scale-105">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3m6-14h.01M9 11h.01M9 15h.01M13 7h.01M13 11h.01M13 15h.01"/></svg>
                                </span>
                            </div>
                        </div>

                        <div class="group rounded-2xl border border-denah-ink/10 bg-white p-6 shadow-soft border-t-4 border-t-denah-terracotta hover:shadow-warm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-denah-ink-muted">{{ __('Total Lokasi Permukiman') }}</p>
                                    <p class="mt-1 text-2xl font-bold text-denah-ink">{{ number_format($totalLocVal, 0, ',', '.') }}</p>
                                </div>
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta group-hover:scale-105">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                </span>
                            </div>
                        </div>

                        <div class="group rounded-2xl border border-denah-ink/10 bg-white p-6 shadow-soft border-t-4 border-t-denah-terracotta hover:shadow-warm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-denah-ink-muted">{{ __('Total Luas Terpetakan') }}</p>
                                    <p class="mt-1 text-2xl font-bold text-denah-ink">{{ number_format($totalAreaVal, 0, ',', '.') }} <span class="text-sm font-semibold text-denah-ink-muted">Ha</span></p>
                                </div>
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta group-hover:scale-105">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-denah-ink/10 bg-denah-cream/40 p-6 shadow-soft">
                        <p class="text-sm leading-relaxed text-denah-ink-muted">
                            {{ __('Lihat sebaran kawasan permukiman secara langsung pada peta interaktif kami.') }}
                        </p>
                        <a href="{{ route('peta') }}" wire:navigate
                           class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-denah-terracotta px-5 py-3 text-sm font-semibold text-white shadow-soft hover:bg-denah-terracotta-dark hover:shadow-warm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            {{ __('Jelajahi Peta') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>