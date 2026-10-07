<div class="space-y-6">
    <x-admin.page-header title="Import Data Permukiman" description="Import data permukiman dari file CSV. Unduh template terlebih dahulu lalu sesuaikan kolomnya." />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Form import --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-1 font-display text-base font-bold text-gray-900">Upload File CSV</h3>
            <p class="mb-4 text-sm text-gray-400">Kolom yang dikenali: name, year, province, regency, district, village, settlement_count, area_size, status, description.</p>

            <input type="file" wire:model="file" accept=".csv,.txt" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream hover:file:bg-denah-terracotta-light" />
            @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" wire:click="import" @if (! $file) disabled @endif class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light disabled:opacity-50">Import Sekarang</button>
                <button type="button" wire:click="downloadTemplate" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Unduh Template</button>
            </div>

            @if ($imported > 0)
                <p class="mt-4 rounded-lg bg-green-50 px-3 py-2 text-sm font-medium text-green-700">{{ $imported }} data berhasil diimport.</p>
            @endif
            @if ($errors)
                <div class="mt-4 max-h-40 overflow-y-auto rounded-lg bg-red-50 p-3 text-xs text-red-700">
                    @foreach ($errors as $e)
                        <p>{{ $e }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Petunjuk --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-3 font-display text-base font-bold text-gray-900">Petunjuk Kolom</h3>
            <ul class="space-y-2 text-sm text-gray-600">
                <li><span class="font-semibold text-gray-800">name</span> — nama wilayah permukiman (wajib)</li>
                <li><span class="font-semibold text-gray-800">year</span> — tahun pemetaan (mis. 2024)</li>
                <li><span class="font-semibold text-gray-800">province / regency / district / village</span> — hierarki wilayah</li>
                <li><span class="font-semibold text-gray-800">settlement_count</span> — jumlah lokasi permukiman</li>
                <li><span class="font-semibold text-gray-800">area_size</span> — luas dalam hektar (Ha)</li>
                <li><span class="font-semibold text-gray-800">status</span> — draft / published</li>
            </ul>
        </div>
    </div>

    {{-- Preview --}}
    @if ($headers)
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-3 font-display text-base font-bold text-gray-900">Pratinjau (10 baris pertama)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-400">
                        <tr>
                            @foreach ($headers as $h)
                                <th class="py-2 pr-3">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($preview as $row)
                            <tr>
                                @foreach ($headers as $i => $h)
                                    <td class="py-2 pr-3 text-gray-600">{{ $row[$i] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td class="py-6 text-center text-gray-400" colspan="{{ count($headers) }}">Tidak ada baris data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
