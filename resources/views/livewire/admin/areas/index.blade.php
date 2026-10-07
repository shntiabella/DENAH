<div class="space-y-6">
    <x-admin.page-header title="Kelola Wilayah Administrasi" description="Kelola hierarki wilayah: Provinsi → Kabupaten/Kota → Kecamatan → Desa/Kelurahan." />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-display text-base font-bold text-gray-900">{{ $editingId ? 'Edit Wilayah' : 'Tambah Wilayah' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Wilayah</label>
                    <input type="text" wire:model="name" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Kode</label>
                        <input type="text" wire:model="code" placeholder="17.01.02.001" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Level</label>
                        <select wire:model.live="level" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta">
                            <option value="provinsi">Provinsi</option>
                            <option value="kabupaten">Kabupaten/Kota</option>
                            <option value="kecamatan">Kecamatan</option>
                            <option value="desa">Desa/Kelurahan</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Induk (Parent)</label>
                    <select wire:model="parentId" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta">
                        <option value="">— Tanpa Induk —</option>
                        @foreach ($parents as $p)
                            <option value="{{ $p->id }}">{{ $p->levelLabel() }} — {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Latitude</label>
                        <input type="number" step="any" wire:model="lat" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Longitude</label>
                        <input type="number" step="any" wire:model="lng" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">{{ $editingId ? 'Perbarui' : 'Tambah' }}</button>
                    @if ($editingId)
                        <button type="button" wire:click="resetForm" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                    @endif
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="mb-4 grid gap-2 sm:grid-cols-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama wilayah..." class="rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta" />
                <select wire:model.live="filterLevel" class="rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta">
                    <option value="all">Semua Level</option>
                    <option value="provinsi">Provinsi</option>
                    <option value="kabupaten">Kabupaten/Kota</option>
                    <option value="kecamatan">Kecamatan</option>
                    <option value="desa">Desa/Kelurahan</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-400">
                        <tr>
                            <th class="py-2 pr-3">Nama</th>
                            <th class="py-2 pr-3">Kode</th>
                            <th class="py-2 pr-3">Level</th>
                            <th class="py-2 pr-3">Induk</th>
                            <th class="py-2 pr-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($areas as $area)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pr-3 font-medium text-gray-800">{{ $area->name }}</td>
                                <td class="py-3 pr-3 text-gray-500">{{ $area->code ?? '—' }}</td>
                                <td class="py-3 pr-3">
                                    <span class="rounded-full bg-denah-terracotta/10 px-2 py-0.5 text-xs font-semibold text-denah-terracotta">{{ $area->levelLabel() }}</span>
                                </td>
                                <td class="py-3 pr-3 text-gray-500">{{ $area->parent?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap py-3 pr-3">
                                    <button wire:click="edit({{ $area->id }})" class="text-xs font-semibold text-denah-terracotta hover:underline">Edit</button>
                                    <button wire:click="confirmDelete('area', {{ $area->id }}, 'Hapus wilayah &quot;{{ $area->name }}&quot;?')" class="ml-2 text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada wilayah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $areas->links() }}</div>
        </div>
    </div>

    <x-admin.confirm-modal />
</div>

