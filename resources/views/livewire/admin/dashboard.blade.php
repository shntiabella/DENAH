<div class="space-y-6">
    {{-- Banner Header dengan Foto / Gambar --}}
    <div class="relative overflow-hidden rounded-xl bg-gray-900 p-6 text-white shadow-sm">
        {{-- Gambar Latar Belakang (Ganti path sesuai file kamu di folder public/images/) --}}
        <img src="{{ asset('public/img/beranda.jpg') }}" alt="Header Dashboard" class="absolute inset-0 h-full w-full object-cover opacity-30">
        
        {{-- Overlay Gradasi (Agar teks tetap mudah dibaca) --}}
        <div class="absolute inset-0 bg-gradient-to-r from-gray-900/90 via-gray-900/50 to-transparent"></div>

        {{-- Konten Teks Header --}}
        <div class="relative z-10 max-w-xl">
            <h1 class="font-display text-2xl font-bold">Selamat Datang di Sistem Permukiman</h1>
            <p class="mt-2 text-sm text-gray-200">
                Kelola, pantau, dan analisis pemetaan wilayah permukiman secara real-time.
            </p>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total Data Permukiman', 'value' => number_format($totalRecords), 'sub' => $totalPublished.' published', 'icon' => 'M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z'],
            ['label' => 'Total Lokasi Permukiman', 'value' => number_format($totalLokasi), 'sub' => 'akumulasi settlement_count', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-6 0'],
            ['label' => 'Total Luas', 'value' => number_format($totalLuas, 2).' Ha', 'sub' => 'akumulasi seluruh wilayah', 'icon' => 'M4 19h16M6 16v-5M10 16V7M14 16v-8M18 16V5'],
            ['label' => 'Total Wilayah', 'value' => number_format($totalWilayah), 'sub' => $totalKabupaten.' kab · '.$totalKecamatan.' kec · '.$totalDesa.' desa', 'icon' => 'M3 10h18M7 15h4m-4 0v4m0-4l2-2m8 2h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $card['label'] }}</p>
                        <p class="mt-2 font-display text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ $card['sub'] }}</p>
                    </div>
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-denah-terracotta/10 text-denah-terracotta">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Quick actions --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="font-display text-base font-bold text-gray-900">Aksi Cepat</h2>
        <div class="mt-3 flex flex-wrap gap-2">
            <a href="{{ route('admin.settlement-areas.index') }}" wire:navigate class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">+ Tambah Data Permukiman</a>
            <a href="{{ route('admin.import.index') }}" wire:navigate class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Import Data</a>
            <a href="{{ route('admin.areas.index') }}" wire:navigate class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kelola Wilayah</a>
            <a href="{{ route('admin.statistik.index') }}" wire:navigate class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Lihat Statistik</a>
            <a href="{{ route('admin.users.index') }}" wire:navigate class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kelola User</a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Data per tahun --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="font-display text-base font-bold text-gray-900">Total Luas per Tahun (Ha)</h2>
            <p class="mt-1 text-sm text-gray-400">Data permukiman berstatus published</p>
            <div class="mt-5 space-y-3">
                @forelse ($perTahun as $row)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-semibold text-gray-800">{{ $row['year'] }}</span>
                            <span class="font-medium text-gray-500">{{ number_format($row['total_area_ha'], 2) }} Ha</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-denah-terracotta transition-all duration-500" style="width: {{ ($row['total_area_ha'] / $maxTahun) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Belum ada data.</p>
                @endforelse
            </div>
        </div>

        {{-- Log aktivitas terbaru --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-base font-bold text-gray-900">Log Aktivitas Terbaru</h2>
            <ul class="mt-4 space-y-3">
                @forelse ($aktivitas as $log)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-denah-terracotta"></span>
                        <div class="min-w-0">
                            <p class="text-gray-700">
                                <span class="font-semibold">{{ $log->user?->name ?? 'Sistem' }}</span>
                                — {{ $log->description ?? $log->action }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $log->created_at?->diffForHumans() }} · {{ $log->module }}</p>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-400">Belum ada aktivitas.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Per kabupaten/kota --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="font-display text-base font-bold text-gray-900">Wilayah per Kabupaten/Kota</h2>
        <p class="mt-1 text-sm text-gray-400">Hanya wilayah berstatus published</p>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @forelse ($perRegency as $reg)
                <div class="rounded-lg border border-gray-100 p-4">
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-700">{{ $reg->regency }}</span>
                        <span class="font-semibold text-gray-900">{{ number_format($reg->total_area_ha, 2) }} Ha</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-denah-terracotta transition-all duration-500" style="width: {{ ($reg->total_area_ha / $maxRegency) * 100 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada data permukiman.</p>
            @endforelse
        </div>
    </div>
</div>