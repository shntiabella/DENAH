<div class="bg-[#faf8f5] text-denah-ink">
    {{-- ============ HERO ============ --}}
    <section class="bg-cover bg-center bg-no-repeat relative py-16 sm:py-24"
             style="background-image: url('{{ $methodology->background_image ? asset('storage/' . $methodology->background_image) : asset('img/background2.jpg') }}');">
        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="bg-[#faf8f5]/85 backdrop-blur-md p-8 sm:p-10 rounded-2xl border border-white/60 shadow-lg max-w-2xl relative z-10">
                <span class="inline-flex items-center gap-2 px-3 py-1 bg-white rounded-full text-xs font-semibold text-[#c86d51] shadow-sm mb-4">
                    {{ __('Metodologi') }}
                </span>
                <h1 class="max-w-3xl font-display text-4xl font-extrabold tracking-tight text-[#2c221e] sm:text-5xl">
                    {{ __('Bagaimana Permukiman Dipetakan?') }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-[#5a4d46]">
                    {{ __('Pemetaan permukiman pada DENAH dilakukan melalui beberapa tahapan pengolahan data spasial dan klasifikasi citra satelit. Seluruh proses utama dilakukan menggunakan Google Earth Engine (GEE) dan hasilnya kemudian diintegrasikan ke dalam WebGIS DENAH.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- ============ PENJELASAN (rich text dari CMS) ============ --}}
    <section class="py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl border border-denah-ink/10 bg-[url('/img/background2.jpg')] bg-cover bg-center p-6 shadow-soft sm:p-8">
                <div class="absolute inset-0 bg-white/80 backdrop-blur-[1px]"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 border-b border-denah-ink/10 pb-4">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </span>
                        <h2 class="font-display text-2xl font-extrabold tracking-tight text-denah-ink sm:text-3xl">{{ __('Tentang Pemrosesan Data') }}</h2>
                    </div>
                    <div class="mt-4 leading-relaxed text-denah-ink">
                        {!! $methodology->t('body') !!}
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ============ TAHAPAN METODOLOGI — 7 TAHAP DETAIL ============ --}}
    <section class="pb-20 sm:pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <h2 class="font-display text-2xl font-extrabold tracking-tight text-denah-ink sm:text-3xl">{{ __('Tahapan Metodologi Pemetaan') }}</h2>
                <p class="mt-3 text-sm text-denah-ink-muted">{{ __('Tujuh tahapan pengolahan citra satelit hingga penyajian WebGIS DENAH.') }}</p>
            </div>

            @php
                $stages = [
                    ['no' => '01', 'title' => __('Citra Satelit'), 'desc' => __('Menggunakan citra Sentinel-2 dan Satellite Embedding untuk wilayah Provinsi Bengkulu pada periode 2021–2025.')],
                    ['no' => '02', 'title' => __('Pra-pemrosesan'), 'desc' => __('Citra difilter berdasarkan wilayah dan kualitas awan menggunakan Cloud Score+, kemudian dibuat komposit tahunan. Ditambahkan indeks NDBI untuk membantu membedakan karakteristik objek permukaan.')],
                    ['no' => '03', 'title' => __('Penentuan Sampel'), 'desc' => __('Sampel latih ditentukan secara manual menjadi dua kelas, yaitu permukiman dan non-permukiman.')],
                    ['no' => '04', 'title' => __('Klasifikasi Random Forest'), 'desc' => __('Sampel digunakan untuk melatih algoritma Random Forest dengan 150 pohon untuk mengklasifikasikan piksel menjadi permukiman dan non-permukiman.')],
                    ['no' => '05', 'title' => __('Hasil Klasifikasi'), 'desc' => __('Hasil klasifikasi menghasilkan data spasial permukiman untuk setiap tahun 2021–2025 di Provinsi Bengkulu.')],
                    ['no' => '06', 'title' => __('Ekspor & Pengolahan Data'), 'desc' => __('Hasil klasifikasi diekspor dari Google Earth Engine dan diolah menjadi data spasial yang kemudian disimpan dalam database DENAH.')],
                    ['no' => '07', 'title' => __('WebGIS & Statistik'), 'desc' => __('Data ditampilkan pada WebGIS DENAH dalam bentuk peta interaktif, informasi wilayah, serta statistik permukiman berdasarkan tahun dan wilayah administrasi.')],
                ];
            @endphp

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($stages as $stage)
                    <div class="group relative flex flex-col rounded-2xl border border-denah-ink/10 bg-white p-6 shadow-soft hover:-translate-y-1 hover:shadow-warm">
                        <div class="flex items-center justify-between">
                            <span class="font-display text-3xl font-extrabold tracking-tight text-denah-terracotta/25">{{ $stage['no'] }}</span>
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta group-hover:bg-denah-terracotta group-hover:text-white">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                        </div>
                        <h3 class="mt-4 font-display text-base font-bold text-denah-ink">{{ $stage['title'] }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-denah-ink-muted">{{ $stage['desc'] }}</p>
                        @if (! empty($stage['points']))
                            <ul class="mt-3 space-y-1.5">
                                @foreach ($stage['points'] as $point)
                                    <li class="flex gap-1.5 text-xs leading-relaxed text-denah-ink-muted">
                                        <span class="mt-0.5 h-1.5 w-1.5 shrink-0 rounded-full bg-denah-terracotta"></span>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if (! $loop->last)
                            <span class="pointer-events-none absolute -right-3 top-1/2 z-10 hidden -translate-y-1/2 text-denah-terracotta/50 lg:block">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 7l5 5-5 5"/></svg>
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-10 rounded-2xl border border-denah-ink/10 bg-denah-cream/40 p-6 shadow-soft sm:p-8">
                <p class="text-center text-xs font-semibold uppercase tracking-[0.18em] text-denah-terracotta">{{ __('Ringkasan Alur') }}</p>
                <div class="mt-5 flex flex-wrap items-center justify-center gap-y-3">
                    @foreach ([
                        __('Sentinel-2 + Satellite Embedding'),
                        __('Pra-pemrosesan & Cloud Masking'),
                        __('Sampel Permukiman / Non-Permukiman'),
                        __('Random Forest (100 Trees)'),
                        __('Klasifikasi Permukiman 2021–2025'),
                        __('Ekspor & Pengolahan Data'),
                        __('WebGIS & Statistik'),
                    ] as $flow)
                        <span class="flex items-center gap-2">
                            <span class="rounded-full border border-denah-terracotta/30 bg-white px-3 py-1.5 text-xs font-semibold text-denah-ink shadow-sm">{{ $flow }}</span>
                            @if (! $loop->last)
                                <svg class="h-4 w-4 text-denah-terracotta" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 7l5 5-5 5"/></svg>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>