<div class="space-y-6">
    <x-admin.page-header title="Fitur Utama" description="Kelola card fitur yang ditampilkan di halaman beranda." />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-display text-base font-bold text-gray-900">{{ $editingId ? 'Edit Fitur' : 'Tambah Fitur' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Judul</label>
                    <input type="text" wire:model="title" wire:model.blur="title"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                    <textarea wire:model="description" wire:model.blur="description" rows="3"
                              class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"></textarea>
                    @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Icon</label>
                    <select wire:model="icon" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta">
                        @foreach ($icons as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
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
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-400">
                        <tr><th class="py-2 pr-3">Urut</th><th class="py-2 pr-3">Judul</th><th class="py-2 pr-3">Deskripsi</th><th class="py-2 pr-3">Icon</th><th class="py-2 pr-3">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($features as $feature)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap py-3 pr-3">
                                    <div class="flex items-center gap-1 text-gray-400">
                                        <button wire:click="move({{ $feature->id }}, 'up')" class="hover:text-gray-700">&uarr;</button>
                                        <button wire:click="move({{ $feature->id }}, 'down')" class="hover:text-gray-700">&darr;</button>
                                    </div>
                                </td>
                                <td class="py-3 pr-3 font-medium text-gray-800">{{ $feature->title }}</td>
                                <td class="max-w-xs truncate py-3 pr-3 text-gray-500">{{ $feature->description }}</td>
                                <td class="py-3 pr-3 text-gray-500">{{ $feature->icon }}</td>
                                <td class="whitespace-nowrap py-3 pr-3">
                                    <button wire:click="edit({{ $feature->id }})" class="text-xs font-semibold text-denah-terracotta hover:underline">Edit</button>
                                    <button wire:click="confirmDelete('feature', {{ $feature->id }}, 'Hapus fitur &quot;{{ $feature->title }}&quot;?')" class="ml-2 text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada fitur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <x-admin.confirm-modal />
</div>
