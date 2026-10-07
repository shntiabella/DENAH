<div class="space-y-6">
    <x-admin.page-header title="Statistik Data Permukiman" description="Ringkasan agregat data permukiman dari database. Gunakan filter untuk melihat per wilayah & tahun." />

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Tahun</label>
                <select wire:model.live="year" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta">
                    <option value="">Semua Tahun</option>
                    @foreach ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Kabupaten/Kota</label>
                <select wire:model.live="regencyId" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta">
                    <option value="">Semua Kabupaten/Kota</option>
                    @foreach ($regencies as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Kecamatan</label>
                <select wire:model.live="districtId" @if (! $regencyId) disabled @endif class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta disabled:bg-gray-50">
                    <option value="">Semua Kecamatan</option>
                    @foreach ($districts as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Desa/Kelurahan</label>
                <select wire:model.live="villageId" @if (! $districtId) disabled @endif class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta disabled:bg-gray-50">
                    <option value="">Semua Desa/Kelurahan</option>
                    @foreach ($villages as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @php
        $totals = $overview['totals'];
        $maxYear = collect($overview['by_year'])->max('total_area_ha') ?: 1;
        $maxRegency = collect($overview['by_regency'])->max('total_area_ha') ?: 1;
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total Lokasi', 'value' => number_format($totals['total_locations']), 'sub' => 'lokasi permukiman'],
            ['label' => 'Total Luas', 'value' => number_format($totals['total_area_ha'], 2).' Ha', 'sub' => 'akumulasi luas'],
            ['label' => 'Total Record', 'value' => number_format($totals['total_records']), 'sub' => 'baris data'],
            ['label' => 'Desa Terdata', 'value' => number_format($totals['total_villages']), 'sub' => $totals['total_districts'].' kecamatan'],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $card['label'] }}</p>
                <p class="mt-2 font-display text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $card['sub'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-base font-bold text-gray-900">Perkembangan per Tahun</h2>
            <div class="mt-5 space-y-3">
                @forelse ($overview['by_year'] as $row)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="font-semibold text-gray-800">{{ $row['year'] }}</span>
                            <span class="text-gray-500">{{ number_format($row['total_area_ha'], 2) }} Ha</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-denah-terracotta" style="width: {{ ($row['total_area_ha'] / $maxYear) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Belum ada data.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-base font-bold text-gray-900">Per Kabupaten/Kota</h2>
            <div class="mt-5 space-y-3">
                @forelse ($overview['by_regency'] as $row)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="font-semibold text-gray-800">{{ $row['regency'] }}</span>
                            <span class="text-gray-500">{{ number_format($row['total_area_ha'], 2) }} Ha</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-denah-terracotta" style="width: {{ ($row['total_area_ha'] / $maxRegency) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Belum ada data.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-base font-bold text-gray-900">Ranking Kecamatan</h2>
            <div class="mt-4 space-y-2">
                @forelse ($overview['ranking_districts'] as $i => $r)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-denah-terracotta/10 text-xs font-bold text-denah-terracotta">{{ $i + 1 }}</span>
                        <span class="flex-1 truncate text-gray-700">{{ $r['name'] }}</span>
                        <span class="font-semibold text-gray-900">{{ number_format($r['total_area_ha'], 2) }} Ha</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Belum ada data.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="font-display text-base font-bold text-gray-900">Ranking Desa/Kelurahan</h2>
            <div class="mt-4 space-y-2">
                @forelse ($overview['ranking_villages'] as $i => $r)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-denah-terracotta/10 text-xs font-bold text-denah-terracotta">{{ $i + 1 }}</span>
                        <span class="flex-1 truncate text-gray-700">{{ $r['name'] }}</span>
                        <span class="font-semibold text-gray-900">{{ number_format($r['total_area_ha'], 2) }} Ha</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Belum ada data.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>


