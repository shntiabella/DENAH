<div class="space-y-6">
    <x-admin.page-header title="Data dalam Angka" description="Kelola 4 item statistik pada section Permukiman dalam Data." />

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Form --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-display text-base font-bold text-gray-900">{{ $editingId ? 'Edit Statistik' : 'Tambah Statistik' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Label</label>
                    <input type="text" wire:model="label" wire:model.blur="label"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Nilai Angka</label>
                        <input type="number" step="any" wire:model="value" wire:model.blur="value"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Satuan</label>
                        <input type="text" wire:model="unit" wire:model.blur="unit" placeholder="Ha / wilayah"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Icon</label>
                    <select wire:model="icon" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta">
                        @foreach ($icons as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" wire:model="counter" class="rounded border-gray-300 text-denah-terracotta focus:ring-denah-terracotta" />
                    Animasi hitung naik (counter)
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">{{ $editingId ? 'Perbarui' : 'Tambah' }}</button>
                    @if ($editingId)
                        <button type="button" wire:click="resetForm" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                    @endif
                </div>
            </form>
        </div>
        {{-- List --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-400">
                        <tr>
                            <th class="py-2 pr-3">Urut</th>
                            <th class="py-2 pr-3">Label</th>
                            <th class="py-2 pr-3">Nilai</th>
                            <th class="py-2 pr-3">Icon</th>
                            <th class="py-2 pr-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($stats as $stat)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap py-3 pr-3">
                                    <div class="flex items-center gap-1 text-gray-400">
                                        <button wire:click="move({{ $stat->id }}, 'up')" class="hover:text-gray-700">&uarr;</button>
                                        <button wire:click="move({{ $stat->id }}, 'down')" class="hover:text-gray-700">&darr;</button>
                                    </div>
                                </td>
                                <td class="py-3 pr-3 font-medium text-gray-800">{{ $stat->label }}</td>
                                <td class="py-3 pr-3 text-gray-700">{{ $stat->value }} {{ $stat->unit }}</td>
                                <td class="py-3 pr-3 text-gray-500">{{ $stat->icon }}</td>
                                <td class="whitespace-nowrap py-3 pr-3">
                                    <button wire:click="edit({{ $stat->id }})" class="text-xs font-semibold text-denah-terracotta hover:underline">Edit</button>
                                    <button wire:click="confirmDelete('stat', {{ $stat->id }}, 'Hapus statistik &quot;{{ $stat->label }}&quot;?')" class="ml-2 text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada statistik.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-admin.confirm-modal />
</div>

