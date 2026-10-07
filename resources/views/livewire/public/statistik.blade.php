<div>
    {{-- ============ HEADER ============ --}}
    <section class="relative overflow-hidden bg-denah-dark text-denah-cream">
        @if ($pageBackground)
            <div class="pointer-events-none absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
                 style="background-image: url('{{ asset('storage/' . $pageBackground) }}');"></div>
            <div class="pointer-events-none absolute inset-0 z-10 bg-denah-dark/85 backdrop-blur-[2px]"></div>
        @endif
        <div class="pointer-events-none absolute inset-0 z-10 bg-denah-dots opacity-25"></div>
        <div class="relative z-20 mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2.5 rounded-full border border-white/10 bg-black/40 px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-denah-terracotta-light backdrop-blur-md">
                <span class="h-1.5 w-1.5 rounded-full bg-denah-terracotta"></span>
                {{ __('Data Dinamis dari Database') }}
            </span>
            <h1 class="mt-5 font-display text-3xl font-extrabold tracking-tight text-denah-cream sm:text-4xl">
                {{ __('Statistik Permukiman') }}
            </h1>
            <p class="mt-3 max-w-2xl text-base leading-relaxed text-denah-cream-muted">
                {{ __('Ringkasan persebaran dan klasifikasi permukiman. Gunakan filter untuk melihat statistik per wilayah dan tahun.') }}
            </p>
        </div>
    </section>

    {{-- ============ FILTER ============ --}}
    <section class="border-b border-denah-ink/10 bg-denah-paper/85 shadow-soft backdrop-blur-md">
        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-denah-ink-muted">Tahun</label>
                    <select id="filter-year" class="w-full rounded-lg border border-denah-ink/15 bg-denah-paper px-3 py-2.5 text-sm text-denah-ink outline-none focus:border-denah-terracotta">
                        <option value="">Semua Tahun</option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-denah-ink-muted">Provinsi</label>
                    <select disabled class="w-full cursor-not-allowed rounded-lg border border-denah-ink/15 bg-denah-cream-bg px-3 py-2.5 text-sm text-denah-ink-muted">
                        <option>{{ $province?->name ?? 'Bengkulu' }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-denah-ink-muted">Kabupaten/Kota</label>
                    <select id="filter-regency" class="w-full rounded-lg border border-denah-ink/15 bg-denah-paper px-3 py-2.5 text-sm text-denah-ink outline-none focus:border-denah-terracotta">
                        <option value="">Semua Kabupaten/Kota</option>
                        @foreach ($regencies as $regency)
                            <option value="{{ $regency->name }}">{{ $regency->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-denah-ink-muted">Kecamatan</label>
                    <select id="filter-district" class="w-full rounded-lg border border-denah-ink/15 bg-denah-paper px-3 py-2.5 text-sm text-denah-ink outline-none focus:border-denah-terracotta">
                        <option value="">Semua Kecamatan</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-denah-ink-muted">Desa/Kelurahan</label>
                    <select id="filter-village" class="w-full rounded-lg border border-denah-ink/15 bg-denah-paper px-3 py-2.5 text-sm text-denah-ink outline-none focus:border-denah-terracotta">
                        <option value="">Semua Desa/Kelurahan</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ ISI ============ --}}
    <section class="bg-denah-cream-bg py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-denah-ink/10 bg-denah-paper/90 p-5 shadow-soft">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#374151]">Total Lokasi Permukiman</p>
                    <p id="kpi-locations" class="mt-2 font-display text-3xl font-extrabold text-denah-terracotta">0</p>
                    <p class="mt-1 text-xs text-denah-ink-muted">lokasi terpetakan</p>
                </div>
                <div class="rounded-2xl border border-denah-ink/10 bg-denah-paper/90 p-5 shadow-soft">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#374151]">Total Luas</p>
                    <p id="kpi-area" class="mt-2 font-display text-3xl font-extrabold text-denah-terracotta">0</p>
                    <p class="mt-1 text-xs text-denah-ink-muted">Ha</p>
                </div>
                <div class="rounded-2xl border border-denah-ink/10 bg-denah-paper/90 p-5 shadow-soft">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#374151]">Desa/Kelurahan</p>
                    <p id="kpi-villages" class="mt-2 font-display text-3xl font-extrabold text-denah-terracotta">0</p>
                    <p class="mt-1 text-xs text-denah-ink-muted">wilayah terdata</p>
                </div>
                <div class="rounded-2xl border border-denah-ink/10 bg-denah-paper/90 p-5 shadow-soft">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#374151]">Kecamatan</p>
                    <p id="kpi-districts" class="mt-2 font-display text-3xl font-extrabold text-denah-terracotta">0</p>
                    <p class="mt-1 text-xs text-denah-ink-muted">wilayah terdata</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-denah-ink/10 bg-denah-paper/90 p-6 shadow-soft">
                    <h2 class="font-display text-base font-bold text-[#111827]">Perkembangan per Tahun</h2>
                    <p class="mt-1 text-xs text-[#374151]">Total luas permukiman (Ha) per tahun</p>
                    <div class="mt-4 h-72"><canvas id="chart-year"></canvas></div>
                </div>
                <div class="rounded-2xl border border-denah-ink/10 bg-denah-paper/90 p-6 shadow-soft">
                    <h2 class="font-display text-base font-bold text-[#111827]">Per Kabupaten/Kota</h2>
                    <p class="mt-1 text-xs text-[#374151]">Total luas permukiman (Ha) per kabupaten/kota</p>
                    <div class="mt-4 h-72"><canvas id="chart-regency"></canvas></div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-denah-ink/10 bg-denah-paper/90 p-6 shadow-soft">
                    <h2 class="font-display text-base font-bold text-[#111827]">Ranking Kecamatan</h2>
                    <p class="mt-1 text-xs text-[#374151]">10 kecamatan teratas</p>
                    <div id="ranking-districts" class="mt-4 space-y-2"></div>
                </div>
                <div class="rounded-2xl border border-denah-ink/10 bg-denah-paper/90 p-6 shadow-soft">
                    <h2 class="font-display text-base font-bold text-[#111827]">Ranking Desa/Kelurahan</h2>
                    <p class="mt-1 text-xs text-[#374151]">10 desa/kelurahan teratas</p>
                    <div id="ranking-villages" class="mt-4 space-y-2"></div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

<script type="application/json" id="stats-data">@json(['regencies' => $regenciesJson, 'districts' => $districtsJson, 'villages' => $villagesJson])</script>

@script
<script>
    const statsData = JSON.parse(document.getElementById('stats-data').textContent);
    const regencies = statsData.regencies || [];
    const districts = statsData.districts || [];
    const villages = statsData.villages || [];

    const regencyIdByName = {};
    regencies.forEach(r => { if (r && r.name) regencyIdByName[r.name] = r.id; });

    let yearChart = null;
    let regencyChart = null;

    const fmt = (n) => new Intl.NumberFormat('id-ID').format(n);
    const fmtArea = (n) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(n || 0);

    function populateDistricts(regencyName) {
        const sel = document.getElementById('filter-district');
        if (!sel) return;
        sel.innerHTML = '<option value="">Semua Kecamatan</option>';
        let items = districts;
        if (regencyName) {
            const regencyId = regencyIdByName[regencyName];
            let matched = regencyId != null ? districts.filter(d => d.parent_id === regencyId) : [];
            if (!matched.length) matched = districts.filter(d => d.parent === regencyName);
            if (matched.length) items = matched;
        }
        items.forEach(d => {
            const o = document.createElement('option');
            o.value = d.name; o.textContent = d.name;
            sel.appendChild(o);
        });
    }

    function populateVillages(districtName) {
        const sel = document.getElementById('filter-village');
        if (!sel) return;
        sel.innerHTML = '<option value="">Semua Desa/Kelurahan</option>';
        let items = villages;
        if (districtName) {
            const district = districts.find(d => d.name === districtName);
            let matched = district && district.id != null ? villages.filter(v => v.parent_id === district.id) : [];
            if (!matched.length) matched = villages.filter(v => v.parent === districtName);
            if (matched.length) items = matched;
        }
        items.forEach(v => {
            const o = document.createElement('option');
            o.value = v.name; o.textContent = v.name;
            sel.appendChild(o);
        });
    }

    function buildUrl() {
        const p = new URLSearchParams();
        const y = document.getElementById('filter-year').value; if (y) p.set('year', y);
        const r = document.getElementById('filter-regency').value; if (r) p.set('regency', r);
        const d = document.getElementById('filter-district').value; if (d) p.set('district', d);
        const v = document.getElementById('filter-village').value; if (v) p.set('village', v);
        return '/api/settlement-stats?' + p.toString();
    }

    function renderRanking(el, list) {
        el.innerHTML = list.map((r, i) => '<div class="flex items-center gap-2 text-sm"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-denah-terracotta/10 text-xs font-bold text-denah-terracotta">' + (i + 1) + '</span><span class="flex-1 truncate text-denah-ink">' + r.name + '</span><span class="font-semibold text-denah-ink-muted">' + fmtArea(r.total_area_ha) + ' Ha</span></div>').join('') || '<p class="text-xs text-denah-ink-muted">Belum ada data.</p>';
    }

    function renderYearChart(byYear) {
        const ctx = document.getElementById('chart-year');
        if (!ctx || typeof Chart === 'undefined') return;
        const labels = byYear.map(x => x.year);
        const values = byYear.map(x => x.total_area_ha);
        if (yearChart) { yearChart.data.labels = labels; yearChart.data.datasets[0].data = values; yearChart.update(); }
        else {
            yearChart = new Chart(ctx, { type: 'line', data: { labels, datasets: [{ label: 'Total Luas (Ha)', data: values, borderColor: '#C6653C', backgroundColor: 'rgba(198,101,60,0.14)', fill: true, tension: 0.3, pointBackgroundColor: '#9E4E2C', pointBorderColor: '#C6653C' }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { title: { display: true, text: 'Luas (Ha)', color: '#4B5563' }, ticks: { color: '#4B5563' }, grid: { color: '#EFE9DF' } }, x: { title: { display: true, text: 'Tahun', color: '#4B5563' }, ticks: { color: '#1A1A1A' }, grid: { display: false } } } } });
        }
    }

    function renderRegencyChart(byRegency) {
        const ctx = document.getElementById('chart-regency');
        if (!ctx || typeof Chart === 'undefined') return;
        const labels = byRegency.map(x => x.regency);
        const values = byRegency.map(x => x.total_area_ha);
        if (regencyChart) { regencyChart.data.labels = labels; regencyChart.data.datasets[0].data = values; regencyChart.update(); }
        else {
            regencyChart = new Chart(ctx, { type: 'bar', data: { labels, datasets: [{ label: 'Total Luas (Ha)', data: values, backgroundColor: '#C9A083', borderColor: '#C6653C', borderWidth: 1, borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { title: { display: true, text: 'Luas (Ha)', color: '#4B5563' }, ticks: { color: '#4B5563' }, grid: { color: '#EFE9DF' } }, y: { ticks: { color: '#1A1A1A' }, grid: { display: false } } } } });
        }
    }

    function render(data) {
        const t = data.totals || {};
        document.getElementById('kpi-locations').textContent = fmt(t.total_locations || 0);
        document.getElementById('kpi-area').textContent = (t.total_area_ha || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
        document.getElementById('kpi-villages').textContent = fmt(t.total_villages || 0);
        document.getElementById('kpi-districts').textContent = fmt(t.total_districts || 0);

        renderRanking(document.getElementById('ranking-districts'), data.ranking_districts || []);
        renderRanking(document.getElementById('ranking-villages'), data.ranking_villages || []);
        renderYearChart(data.by_year || []);
        renderRegencyChart(data.by_regency || []);
    }

    function load() {
        fetch(buildUrl()).then(r => r.json()).then(render);
    }

    document.getElementById('filter-regency').addEventListener('change', function () {
        populateDistricts(this.value);
        populateVillages('');
        load();
    });
    document.getElementById('filter-district').addEventListener('change', function () {
        populateVillages(this.value);
        load();
    });
    document.getElementById('filter-year').addEventListener('change', load);
    document.getElementById('filter-village').addEventListener('change', load);

    populateDistricts('');
    populateVillages('');
    load();
</script>
@endscript




