<div class="space-y-6">
    <x-admin.page-header title="Data Permukiman" description="Upload langsung file GeoJSON/JSON untuk memperbarui data spasial. Kelola, pilih, dan hapus data secara massal." />

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Upload GeoJSON langsung (menggantikan form manual) --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="mb-1 font-display text-base font-bold text-gray-900">Upload Langsung GeoJSON / JSON</h3>
            <p class="mb-4 text-sm text-gray-400">Pilih file GeoJSON/JSON (Feature atau FeatureCollection). Setiap file disimpan sebagai satu data permukiman beserta geometri, jumlah lokasi (dari nama kawasan/lokasi unik, mis. <code class="rounded bg-gray-100 px-1">NAMOBJ</code> / <code class="rounded bg-gray-100 px-1">LOKASI</code>), dan luas dalam hektar (Ha).</p>

            <input type="file" wire:model="geojsonFile" accept=".json,.geojson,application/json"
                   class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream hover:file:bg-denah-terracotta-light" />
            @error('geojsonFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            @if ($parsedGeojson)
                <div class="mt-4 rounded-lg border border-green-100 bg-green-50 p-4 text-sm">
                    <p class="font-semibold text-green-800">File siap diimpor: {{ $previewName }}</p>
                    <dl class="mt-2 grid grid-cols-3 gap-3 text-xs text-green-700">
                        <div><dt class="font-medium text-green-600">Fitur</dt><dd class="font-bold text-green-800">{{ $previewCount }}</dd></div>
                        <div><dt class="font-medium text-green-600">Luas</dt><dd class="font-bold text-green-800">{{ number_format($previewArea, 4) }} Ha</dd></div>
                        <div><dt class="font-medium text-green-600">Status</dt><dd class="font-bold text-green-800">{{ $status === 'published' ? 'Published' : 'Draft' }}</dd></div>
                    </dl>
                    @if ($geojsonWarning) <p class="mt-2 text-xs text-amber-700">{{ $geojsonWarning }}</p> @endif
                </div>
            @elseif ($geojsonError)
                <p class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $geojsonError }}</p>
            @elseif ($geojsonFile)
                <p class="mt-4 text-xs text-gray-400" wire:loading wire:target="geojsonFile">Memvalidasi file…</p>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" wire:model="status" value="published" class="rounded border-gray-300 text-denah-terracotta focus:ring-denah-terracotta" checked />
                    Simpan sebagai Published
                </label>
                <div class="ml-auto">
                    <button type="button" wire:click="importFile" @if (! $parsedGeojson) disabled @endif
                            class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light disabled:opacity-50">
                        Simpan / Import
                    </button>
                </div>
            </div>
        </div>

        {{-- Dangerous zone: hapus semua --}}
        <div class="rounded-xl border border-red-200 bg-white p-6 shadow-sm">
            <h3 class="mb-1 font-display text-base font-bold text-red-700">Bersihkan Semua</h3>
            <p class="mb-4 text-sm text-gray-400">Mengosongkan seluruh <strong class="text-gray-700">{{ number_format($totalCount) }}</strong> data permukiman dari database secara permanen. Tindakan tidak dapat dibatalkan.</p>
            <button type="button" wire:click="openClearAll"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                Hapus Semua Data Permukiman
            </button>
        </div>
    </div>
{{-- Daftar data --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama…"
                       class="w-56 rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                <span class="text-sm text-gray-500">{{ $selectedCount }} terpilih</span>
            </div>
            <button type="button" wire:click="confirmDelete('bulk', 0, 'Hapus {{ $selectedCount }} data permukiman terpilih secara bersamaan?')"
                    @if ($selectedCount === 0) disabled @endif
                    class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100 disabled:opacity-40">
                Hapus Terpilih ({{ $selectedCount }})
            </button>
        </div>

        <div class="overflow-x-auto">
            <table x-data="{ all: false }" class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="py-3 pr-3">
                            <input type="checkbox" x-model="all" @change="document.querySelectorAll('.area-check').forEach(function (c) { c.checked = all; c.dispatchEvent(new Event('change', { bubbles: true })); })"
                                   class="rounded border-gray-300 text-denah-terracotta focus:ring-denah-terracotta" />
                        </th>
                        <th class="py-3 pr-3">Nama</th>
                        <th class="py-3 pr-3">Tahun</th>
                        <th class="py-3 pr-3">Lokasi</th>
                        <th class="py-3 pr-3">Luas (Ha)</th>
                        <th class="py-3 pr-3">Status</th>
                        <th class="py-3 pr-3">Dibuat</th>
                        <th class="py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($areas as $area)
                        <tr>
                            <td class="py-3 pr-3">
                                <input type="checkbox" wire:model="selected" value="{{ $area->id }}" class="rounded border-gray-300 text-denah-terracotta focus:ring-denah-terracotta area-check" />
                            </td>
                            <td class="py-3 pr-3 font-medium text-gray-800">{{ $area->name }}</td>
                            <td class="py-3 pr-3 text-gray-600">{{ $area->year }}</td>
                            <td class="py-3 pr-3 text-gray-600">{{ number_format($area->settlement_count) }}</td>
                            <td class="py-3 pr-3 text-gray-600">{{ number_format($area->area_size, 4) }}</td>
                            <td class="py-3 pr-3">
                                @if ($area->status === 'published')
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700">Published</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Draft</span>
                                @endif
                            </td>
                            <td class="py-3 pr-3 text-xs text-gray-400">{{ $area->created_at?->format('d M Y') }}</td>
                            <td class="whitespace-nowrap py-3">
                                <button wire:click="toggleStatus({{ $area->id }})" class="text-xs font-semibold text-gray-600 hover:underline">Toggle</button>
                                <button wire:click="confirmDelete('area', {{ $area->id }}, 'Hapus wilayah &quot;{{ $area->name }}&quot;?')" class="ml-2 text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-gray-400">Belum ada data spasial. Upload file GeoJSON di atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $areas->links() }}</div>
    </div>
<x-admin.confirm-modal />

    {{-- Modal "Hapus Semua" dengan konfirmasi ketik HAPUS --}}
    <div x-data x-show="$wire.showClearAllModal" x-cloak
         @keydown.escape.window="$wire.closeClearAll()"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="$wire.closeClearAll()"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-red-50 text-red-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                </span>
                <div class="flex-1">
                    <h3 class="font-display text-lg font-bold text-gray-900">Hapus Semua Data Permukiman</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ini akan mengosongkan seluruh <strong>{{ number_format($totalCount) }}</strong> data permukiman dari database. Tindakan ini permanen dan tidak dapat dibatalkan.
                    </p>
                    <label class="mt-4 block text-sm text-gray-700">Ketik <span class="font-bold text-red-600">HAPUS</span> untuk konfirmasi:</label>
                    <input type="text" wire:model.live="clearAllTyped" placeholder="HAPUS"
                           class="mt-1 w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-red-400 focus:ring-1 focus:ring-red-400" />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="$wire.closeClearAll()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="button" @click="$wire.clearAll()" @if (strtoupper($clearAllTyped) !== 'HAPUS') disabled @endif
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-40">Hapus Semua</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', function () {});
</script>
@endpush