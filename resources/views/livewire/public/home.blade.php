<div>
    {{-- ============ HERO (terang, Minimalis Earthy Light) ============ --}}
    <section class="relative overflow-hidden text-denah-ink">
        {{-- Background dinamis dari CMS (fallback: gambar lokal) --}}
        <img src="{{ $hero->background_image ? asset('storage/' . $hero->background_image) : asset('img/beranda.jpg') }}"
             alt="" aria-hidden="true" loading="lazy" decoding="async" fetchpriority="low"
             class="pointer-events-none absolute inset-0 z-0 h-full w-full object-cover object-center" />
        {{-- Overlay hitam tipis agar foto utama terlihat jelas --}}
        <div class="pointer-events-none absolute inset-0 z-10 bg-black/20 backdrop-blur-[1px]"></div>

        <div class="relative z-20 mx-auto grid max-w-6xl grid-cols-12 items-stretch gap-6 px-4 py-6">
            {{-- Kolom teks (dibungkus box putih transparan agar judul, deskripsi & tombol dijamin terbaca) --}}
            <div class="relative z-20 col-span-12 flex h-full max-w-xl flex-col justify-between rounded-2xl border border-white/40 bg-white/70 p-6 shadow-lg backdrop-blur-md transition-all duration-700 ease-out lg:col-span-6 lg:p-8"
                 x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 80)"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                <span class="inline-flex items-center gap-2.5 rounded-full border border-denah-terracotta/20 bg-white px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-denah-ink shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-denah-terracotta"></span>
                    {{ __('Permukiman Bengkulu') }}
                </span>

                <h1 class="mt-4 font-display text-2xl font-bold leading-tight text-denah-ink lg:text-3xl">
                    {{ __('Pemetaan Permukiman') }}
                    <span class="block text-denah-terracotta">{{ __('Provinsi Bengkulu') }}</span>
                </h1>

                <p class="mt-3 font-display text-xs font-semibold tracking-tight text-denah-terracotta lg:text-sm">
                    {{ __('DENAH — Digital Ecosystem & Navigation for Area Housing') }}
                </p>

                <p class="mt-4 max-w-lg text-xs leading-relaxed text-stone-600 lg:text-sm">
                    {{ __('DENAH merupakan WebGIS yang menyajikan informasi sebaran permukiman di Provinsi Bengkulu berdasarkan hasil pengolahan dan klasifikasi citra satelit. Data pemetaan mencakup periode 2021–2025 sehingga dapat digunakan untuk melihat pola dan perkembangan permukiman dari waktu ke waktu.') }}
                </p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('peta') }}" wire:navigate
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-denah-terracotta px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-denah-terracotta/25 transition-all duration-300 hover:scale-[1.03] hover:bg-denah-terracotta-dark">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        {{ __('Jelajahi Peta') }}
                    </a>
                    <a href="#statistik"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-6 py-3.5 text-sm font-semibold text-denah-ink transition-all duration-300 hover:scale-[1.03] hover:border-denah-terracotta/40 hover:text-denah-terracotta">
                        {{ __('Lihat Statistik') }}
                    </a>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ([__('Peta Interaktif'), __('Data Multi-Tahun'), __('Statistik Spasial'), __('Informasi Wilayah')] as $feature)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-denah-ink-muted shadow-sm">
                            <svg class="h-4 w-4 text-denah-terracotta" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                            {{ $feature }}
                        </span>
                    @endforeach
                </div>
            </div>
            {{-- Kolom visual: kartu peta yang melayang (simetris dengan kartu teks kiri) --}}
            <div class="relative z-20 col-span-12 flex h-full w-full items-center justify-center overflow-hidden rounded-2xl border border-white/50 bg-white/60 shadow-xl backdrop-blur-md transition-all delay-150 duration-700 ease-out lg:col-span-6"
                 x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 200)"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                <div class="col-span-12 flex items-center justify-center p-6 lg:col-span-6">
                    <img src="{{ asset('img/petabengkulu.jpg') }}"
                         alt="{{ __('Peta persebaran permukiman') }}"
                         loading="lazy" decoding="async"
                         class="w-full max-h-[300px] object-contain drop-shadow-xl lg:max-h-[360px]" />
                </div>
            </div>
        </div>

        {{-- Koordinat dekoratif pojok kiri bawah --}}
        <div class="pointer-events-none absolute bottom-5 left-4 z-20 hidden font-mono text-[11px] tracking-[0.22em] text-denah-ink-muted/60 sm:left-6 lg:left-8 xl:block">
            3&#176;47'53"S | 102&#176;15'53"E
        </div>
    </section>
    {{-- ============ TENTANG DENAH (terang) ============ --}}
    <section class="relative scroll-mt-24 overflow-hidden bg-denah-cream-bg py-20 sm:py-24" id="tentang">
        {{-- Dot pattern dekoratif halus warna coklat --}}
        <div class="pointer-events-none absolute -right-14 top-14 hidden h-72 w-72 bg-denah-dots-light opacity-70 lg:block"></div>
        <div class="pointer-events-none absolute -left-14 bottom-10 hidden h-56 w-56 bg-denah-dots-light opacity-50 lg:block"></div>

        <div class="relative mx-auto grid max-w-7xl grid-cols-12 items-center gap-6 px-4 sm:px-6 lg:px-8">
            <div class="col-span-12 flex transition-all duration-700 ease-out lg:col-span-6"
                 x-data="{ shown: false }" x-intersect.once="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                <div class="w-full bg-[#faf7f2] p-6 lg:p-8 rounded-2xl border border-[#ece4d8] shadow-sm">
                <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-denah-terracotta">
                    <span class="h-px w-8 bg-denah-terracotta"></span>{{ __('Tentang DENAH') }}
                </span>
                <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-denah-ink sm:text-4xl">
                    {{ __('Memahami Permukiman dari Data Spasial') }}
                </h2>
                <p class="text-stone-600 leading-relaxed my-4">{{ __('DENAH merupakan WebGIS yang menyajikan informasi spasial mengenai sebaran permukiman di Provinsi Bengkulu berdasarkan pengolahan citra satelit multi-tahun (2021–2025).') }}</p>
                <div class="prose-denah mt-5 space-y-4 text-base leading-relaxed text-denah-ink-muted [&_a]:text-denah-terracotta [&_a]:underline [&_strong]:font-semibold [&_h2]:font-display [&_h2]:text-2xl [&_h2]:font-extrabold [&_h2]:text-denah-ink [&_h3]:font-display [&_h3]:text-lg [&_h3]:font-bold [&_h3]:text-denah-ink">
                    {!! $about->t('body') !!}
                </div>
                <a href="{{ url('/tentang') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-[#c86d51] hover:bg-[#b05a40] text-white font-semibold rounded-xl shadow-md transition-all duration-200">
                    <span>{{ __('Selengkapnya') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                </div>
            </div>
            <div class="col-span-12 flex transition-all delay-150 duration-700 ease-out lg:col-span-6"
                 x-data="{ shown: false }" x-intersect.once="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                @if ($about->image)
                    <div class="w-full h-full bg-white rounded-2xl p-4 shadow-sm border border-stone-200/80 flex items-center justify-center overflow-hidden">
                        <img src="{{ Storage::url($about->image) }}" alt="{{ $about->t('more_link_text') ?: __('Tentang DENAH') }}"
                             loading="lazy" decoding="async"
                             class="max-h-[300px] w-auto object-contain" />
                    </div>
                @else
                    <div class="w-full h-full bg-white rounded-2xl p-4 shadow-sm border border-stone-200/80 flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('img/backround2.jpg') }}" alt="{{ __('Peta Rute DENAH') }}" loading="lazy" decoding="async" class="max-h-[300px] w-auto object-contain">
                    </div>
                @endif

            </div>
        </div>
    </section>

    {{-- ============ DATA DALAM ANGKA (gelap) ============ --}}
    <section class="relative scroll-mt-24 overflow-hidden bg-denah-dark py-20 sm:py-24" id="statistik">
        <div class="pointer-events-none absolute inset-0 bg-denah-dots opacity-20"></div>
        <div class="pointer-events-none absolute -right-40 top-0 h-96 w-96 rounded-full bg-denah-terracotta/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl transition-all duration-700 ease-out"
                 x-data="{ shown: false }" x-intersect.once="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-denah-terracotta">
                    <span class="h-px w-8 bg-denah-terracotta"></span>{{ __('Permukiman dalam Data') }}
                </span>
                <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-denah-cream sm:text-4xl">
                    {{ __('Ringkasan Data Permukiman') }}
                </h2>
                <p class="mt-4 text-base text-denah-cream-muted">
                    {{ __('Statistik berikut akan diperbarui seiring ketersediaan data hasil pemetaan.') }}
                </p>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-denah-terracotta/40"
                         x-data="{
                             current: 0,
                             target: {{ $stat['value'] }},
                             counting: {{ $stat['counter'] ? 'true' : 'false' }},
                             start() {
                                 if (! this.counting) { this.current = this.target; return; }
                                 const duration = 1600;
                                 const t0 = performance.now();
                                 const step = (now) => {
                                     const p = Math.min((now - t0) / duration, 1);
                                     this.current = Math.round((1 - Math.pow(1 - p, 3)) * this.target);
                                     if (p < 1) requestAnimationFrame(step);
                                 };
                                 requestAnimationFrame(step);
                             }
                         }"
                         x-intersect.once="setTimeout(() => start(), {{ $loop->index * 120 }})">
                        {{-- Icon dalam lingkaran outline tipis terracotta --}}
                        <span class="grid h-11 w-11 place-items-center rounded-full border border-denah-terracotta/50 p-3 text-denah-terracotta-light">
                            @if ($stat['icon'] === 'area')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6v14M15 3v14"/></svg>
                            @elseif ($stat['icon'] === 'pins')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
                            @elseif ($stat['icon'] === 'layers')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l9 5 9-5"/></svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="2"/><path stroke-linecap="round" d="M3 9h18M8 2v4M16 2v4"/></svg>
                            @endif
                        </span>
                        <div class="mt-5 flex items-baseline gap-1.5">
                            <span class="font-display text-4xl font-extrabold tracking-tight text-denah-cream"
                                  x-text="counting ? current.toLocaleString('id-ID') : target">{{ $stat['counter'] ? 0 : $stat['value'] }}</span>
                            @if ($stat['unit'])
                                <span class="text-sm font-medium text-denah-cream-muted">{{ $stat['unit'] }}</span>
                            @endif
                        </div>
                        {{-- Divider garis pendek terracotta di bawah angka --}}
                        <div class="mt-3 h-0.5 w-8 rounded-full bg-denah-terracotta"></div>
                        <div class="mt-3 text-sm font-semibold text-denah-cream/90">{{ $stat['label'] }}</div>
                        <div class="mt-1 text-xs text-denah-cream-muted">{{ $stat['hint'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    {{-- ============ FITUR UTAMA (terang) ============ --}}
    <section class="scroll-mt-24 bg-denah-cream-bg py-20 sm:py-24" id="fitur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center transition-all duration-700 ease-out"
                 x-data="{ shown: false }" x-intersect.once="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-denah-terracotta">
                    <span class="h-px w-8 bg-denah-terracotta"></span>{{ __('Fitur Utama') }}<span class="h-px w-8 bg-denah-terracotta"></span>
                </span>
                <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-denah-ink sm:text-4xl">
                    {{ __('Dua Cara Menjelajahi Data Permukiman') }}
                </h2>
                <p class="mt-4 text-base text-denah-ink-muted">
                    {{ __('DENAH mengintegrasikan data hasil klasifikasi citra satelit dari Google Earth Engine (GEE) ke dalam sistem WebGIS interaktif. Pengguna dapat mengeksplorasi persebaran permukiman berdasarkan wilayah administrasi dan tahun, serta melihat informasi statistik yang dihasilkan dari data spasial.') }}
                </p>
            </div>

            <div class="mt-12 grid gap-6 transition-all delay-150 duration-700 ease-out md:grid-cols-2"
                 x-data="{ shown: false }" x-intersect.once="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                @foreach ([
                    ['icon' => 'map', 'title' => __('Peta Interaktif'), 'route' => 'peta', 'text' => __('Jelajahi persebaran permukiman di seluruh Provinsi Bengkulu melalui peta interaktif.')],
                    ['icon' => 'chart', 'title' => __('Statistik'), 'route' => 'statistik', 'text' => __('Lihat informasi jumlah lokasi dan luas area permukiman berdasarkan wilayah dan tahun.')],
                ] as $feature)
                    <a href="{{ route($feature['route']) }}" wire:navigate
                       class="group flex flex-col rounded-2xl border border-denah-sand/70 bg-denah-paper/90 p-7 shadow-soft backdrop-blur-md transition-all duration-300 hover:-translate-y-1 hover:border-denah-terracotta/60 hover:shadow-warm">
                        <span class="grid h-14 w-14 place-items-center rounded-full border border-denah-terracotta/30 bg-denah-terracotta/10 p-3 text-denah-terracotta transition-colors duration-300 group-hover:border-denah-terracotta group-hover:bg-denah-terracotta group-hover:text-denah-cream">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                @if ($feature['icon'] === 'map')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6v14M15 3v14"/>
                                @elseif ($feature['icon'] === 'chart')
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 15l4-4 3 3 5-6"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5M5 21h14"/>
                                @endif
                            </svg>
                        </span>
                        <h3 class="mt-5 font-display text-lg font-bold text-denah-ink">{{ $feature['title'] }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-denah-ink-muted">{{ $feature['text'] }}</p>
                        <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-denah-terracotta">
                            {{ __('Pelajari') }}
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    {{-- ============ PETA INTERAKTIF (dark, senada hero) ============ --}}
    <section class="relative scroll-mt-24 overflow-hidden bg-denah-dark py-20 sm:py-24" id="peta-preview">
        <div class="pointer-events-none absolute inset-0 bg-denah-dots opacity-20"></div>
        <div class="pointer-events-none absolute -left-40 bottom-0 h-96 w-96 rounded-full bg-denah-terracotta/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-32 top-10 h-72 w-72 rounded-full bg-denah-terracotta-light/10 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-[1fr_1.2fr] lg:px-8">

            {{-- ===== Kolom kiri: teks ===== --}}
            <div class="max-w-xl transition-all duration-700 ease-out"
                x-data="{ shown: false }" x-intersect.once="shown = true"
                :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">

                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-denah-terracotta-light backdrop-blur-md">
                    <span class="h-1.5 w-1.5 rounded-full bg-denah-terracotta"></span>
                    {{ __('Peta Interaktif') }}
                </span>

                <h2 class="mt-5 font-display text-3xl font-extrabold tracking-tight text-denah-cream sm:text-4xl">
                    {{ __('Jelajahi Peta Permukiman Interaktif') }}
                </h2>

                <p class="mt-4 text-base leading-relaxed text-denah-cream-muted">
                    {{ __('Lihat persebaran dan perkembangan permukiman Provinsi Bengkulu pada peta interaktif dengan data periode 2021–2025 dan layer administrasi.') }}
                </p>

                <ul class="mt-7 space-y-2.5">
                    @foreach ([__('Data permukiman periode 2021–2025'), __('Navigasi zoom & pan dengan kontrol layer'), __('Legenda klasifikasi permukiman')] as $point)
                        <li class="group flex items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-sm text-denah-cream-muted transition hover:border-white/10 hover:bg-white/5">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-denah-terracotta/15 text-denah-terracotta-light transition group-hover:bg-denah-terracotta group-hover:text-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                            </span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('peta') }}" wire:navigate
                class="mt-9 inline-flex items-center gap-2 rounded-xl bg-denah-terracotta px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-denah-terracotta/25 transition-all duration-300 hover:scale-[1.03] hover:bg-denah-terracotta-dark hover:shadow-xl hover:shadow-denah-terracotta/30">
                    {{ __('Buka Peta Interaktif') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            {{-- ===== Kolom kanan: preview peta ===== --}}
            <div class="relative rounded-3xl bg-gradient-to-br from-white/15 via-white/5 to-denah-terracotta/20 p-[1.5px] shadow-2xl shadow-black/40 transition-all delay-150 duration-700 ease-out"
                x-data="{ shown: false }" x-intersect.once="shown = true"
                :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">

                <div class="relative overflow-hidden rounded-3xl bg-white/[0.06] p-2 backdrop-blur-xl">
                    <x-public.map-visual variant="light" :show-controls="false" class="h-[340px] rounded-2xl sm:h-[420px]" />

                    {{-- Badge kecil melayang di kiri atas --}}
                    <div class="absolute left-4 top-4 flex items-center gap-2 rounded-full border border-white/15 bg-black/40 px-3 py-1.5 text-[11px] font-semibold text-denah-cream shadow-sm backdrop-blur-md">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-denah-terracotta"></span>
                        {{ __('Preview Langsung') }}
                    </div>

                    {{-- Kontrol zoom & layer --}}
                    <div class="absolute right-4 top-4 flex flex-col gap-1.5 rounded-xl border border-white/15 bg-black/40 p-1 shadow-sm backdrop-blur-md">
                        <button type="button" aria-label="{{ __('Perbesar') }}"
                                class="grid h-8 w-8 place-items-center rounded-lg text-denah-cream transition hover:bg-denah-terracotta/20 hover:text-denah-terracotta-light">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <div class="mx-1 h-px bg-white/15"></div>
                        <button type="button" aria-label="{{ __('Perkecil') }}"
                                class="grid h-8 w-8 place-items-center rounded-lg text-denah-cream transition hover:bg-denah-terracotta/20 hover:text-denah-terracotta-light">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" d="M5 12h14"/></svg>
                        </button>
                        <div class="mx-1 h-px bg-white/15"></div>
                        <button type="button" aria-label="{{ __('Layer') }}"
                                class="grid h-8 w-8 place-items-center rounded-lg text-denah-cream transition hover:bg-denah-terracotta/20 hover:text-denah-terracotta-light">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l9 5 9-5"/></svg>
                        </button>
                    </div>

                    {{-- Legend --}}
                    <div class="absolute bottom-4 right-4 w-52 rounded-xl border border-white/15 bg-black/40 p-3.5 shadow-sm backdrop-blur-md">
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-denah-cream-muted">{{ __('Legenda') }}</span>
                        <ul class="mt-2.5 space-y-2 text-xs font-medium text-denah-cream">
                            <li class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-denah-terracotta ring-4 ring-denah-terracotta/25"></span>
                                {{ __('Permukiman') }}
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="w-5 shrink-0 border-t-2 border-dashed border-white/40"></span>
                                {{ __('Batas Administrasi') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- ============ PROSES / METODOLOGI (terang) ============ --}}
    <section class="relative scroll-mt-24 overflow-hidden bg-denah-cream-bg py-20 sm:py-24" id="metodologi">
        <div class="pointer-events-none absolute -left-14 top-16 hidden h-64 w-64 bg-denah-dots-light opacity-60 lg:block"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center transition-all duration-700 ease-out"
                 x-data="{ shown: false }" x-intersect.once="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-denah-terracotta">
                    <span class="h-px w-8 bg-denah-terracotta"></span>{{ __('Metodologi') }}<span class="h-px w-8 bg-denah-terracotta"></span>
                </span>
                <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-denah-ink sm:text-4xl">
                    {{ strip_tags($methodology->t('title')) ?: __('Bagaimana Permukiman Dipetakan?') }}
                </h2>
            </div>

            @php
                $icons = [
                    'satellite' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 10a7 7 0 017-7M4 15a12 12 0 0112-12M10 15a9 9 0 019-9M4 20a16 16 0 0116-16"/>',
                    'layers' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l9 5 9-5"/>',
                    'pins' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/>',
                    'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 15l4-4 3 3 5-6"/>',
                    'map' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6v14M15 3v14"/>',
                    'download' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-3.5-3.5M12 15l3.5-3.5M5 21h14"/>',
                    'globe' => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M12 3c2.5 2.6 3.9 5.7 3.9 9S14.5 18.4 12 21c-2.5-2.6-3.9-5.7-3.9-9S9.5 5.6 12 3z"/>',
                    'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/>',
                ];

                $fallbackSteps = [
                    ['icon' => 'satellite', 'title' => __('Citra Satelit'), 'text' => __('Sentinel-2 & Satellite Embedding untuk wilayah Provinsi Bengkulu pada periode 2021–2025.')],
                    ['icon' => 'layers', 'title' => __('Pra-pemrosesan'), 'text' => __('Cloud masking Cloud Score+ dan penambahan indeks NBR.')],
                    ['icon' => 'pins', 'title' => __('Penentuan Sampel'), 'text' => __('Sampel latih menjadi dua kelas: permukiman vs non-permukiman.')],
                    ['icon' => 'chart', 'title' => __('Klasifikasi Random Forest'), 'text' => __('Klasifikasi menggunakan algoritma Random Forest dengan 100 trees.')],
                    ['icon' => 'map', 'title' => __('Hasil Klasifikasi'), 'text' => __('Data spasial permukiman hasil klasifikasi untuk periode 2021–2025.')],
                    ['icon' => 'download', 'title' => __('Ekspor & Pengolahan Data'), 'text' => __('Ekspor dari Google Earth Engine dan pengolahan data ke dalam database DENAH.')],
                    ['icon' => 'globe', 'title' => __('WebGIS & Statistik'), 'text' => __('Penyajian peta interaktif, informasi wilayah, dan statistik permukiman.')],
                ];

                if ($methodologies->isEmpty()) {
                    $methodologies = collect($fallbackSteps);
                }
            @endphp

            <div class="relative mt-14 transition-all delay-150 duration-700 ease-out"
                 x-data="{ shown: false }" x-intersect.once="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
                {{-- Garis dotted penghubung antar step (desktop) --}}
                <div class="absolute inset-x-24 top-6 hidden border-t-2 border-dotted border-denah-terracotta/40 lg:block" aria-hidden="true"></div>

                <ol class="grid grid-cols-1 gap-3 md:grid-cols-4 lg:grid-cols-7">
                    @foreach ($methodologies as $step)
                        @php
                            $isModel = $step instanceof \App\Models\MethodologyStep;
                            $stepIcon = $isModel ? $step->icon : $step['icon'];
                            $stepTitle = $isModel ? $step->t('title') : $step['title'];
                        @endphp
                        <li class="relative flex flex-col items-center text-center">
                            <span class="relative z-10 grid h-12 w-12 place-items-center rounded-full border border-denah-terracotta/40 bg-denah-cream-bg text-denah-terracotta">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    {!! $icons[$stepIcon] ?? $icons['check'] !!}
                                </svg>
                            </span>
                            <span class="mt-4 font-display text-5xl font-extrabold leading-none tracking-tight text-outline-terracotta" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-3 font-display text-sm font-bold text-denah-ink">{{ $stepTitle }}</h3>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('metodologi') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-xl bg-denah-terracotta px-6 py-3 text-sm font-semibold text-denah-cream shadow-lg shadow-denah-terracotta/25 transition-all duration-300 hover:scale-[1.03] hover:bg-denah-terracotta-light hover:text-denah-dark">
                    {{ __('Lihat Metodologi') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </section>
    {{-- ============ CTA AKHIR (gelap, foto permukiman samar) ============ --}}
    <section class="relative overflow-hidden bg-denah-dark py-24 sm:py-28">
        <img src="https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=1920&q=80"
             alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.remove()"
             class="absolute inset-0 h-full w-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-t from-denah-dark via-denah-dark/85 to-denah-dark/70"></div>
        <div class="pointer-events-none absolute inset-0 bg-denah-dots opacity-20"></div>

        <div class="relative mx-auto max-w-3xl px-4 text-center transition-all duration-700 ease-out sm:px-6 lg:px-8"
             x-data="{ shown: false }" x-intersect.once="shown = true"
             :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'">
            <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-denah-terracotta">
                <span class="h-px w-8 bg-denah-terracotta"></span>{{ __('Mulai Jelajah') }}<span class="h-px w-8 bg-denah-terracotta"></span>
            </span>
            <h2 class="mt-5 font-display text-3xl font-extrabold tracking-tight text-denah-cream sm:text-4xl">
                {{ __('Siap Menjelajahi Data Permukiman?') }}
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-base leading-relaxed text-denah-cream-muted">
                {{ __('Temukan persebaran dan perkembangan permukiman Provinsi Bengkulu secara interaktif.') }}
            </p>
            <div class="mt-9">
                <a href="{{ route('peta') }}" wire:navigate
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-denah-terracotta px-7 py-3.5 text-sm font-semibold text-denah-cream shadow-lg shadow-black/30 transition-all duration-300 hover:scale-[1.03] hover:bg-denah-terracotta-light hover:text-denah-dark">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-6.5-7-11a7 7 0 1114 0c0 4.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    {{ __('Jelajahi Peta Sekarang') }}
                </a>
            </div>
        </div>
    </section>
</div>
