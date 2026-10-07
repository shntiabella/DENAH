<div class="space-y-6">
    <x-admin.page-header title="Metodologi" description="Kelola step proses pemetaan & penjelasan metodologi lengkap." />

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm" x-data="{ lang: 'id' }">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-display text-base font-bold text-gray-900">Penjelasan Metodologi Lengkap</h3>
            <x-admin.lang-tabs />
        </div>

        <div class="mb-4 grid gap-4 sm:grid-cols-2">
            <div x-show="lang === 'id'">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Judul Section (ID)</label>
                <input type="text" wire:model="contentTitleId" maxlength="120"
                       class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                @error('contentTitleId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div x-show="lang === 'en'" x-cloak>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Section Title (EN)</label>
                <input type="text" wire:model="contentTitleEn" maxlength="120"
                       class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                @error('contentTitleEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div x-show="lang === 'id'">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Konten (rich text) — Bahasa Indonesia</label>
            <textarea wire:model="bodyId" rows="6" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta">{{ $bodyId }}</textarea>
            @error('bodyId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-show="lang === 'en'" x-cloak>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Content (rich text) — English</label>
            <textarea wire:model="bodyEn" rows="6" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta">{{ $bodyEn }}</textarea>
            @error('bodyEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="mt-5">
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Gambar Background Halaman</label>
            @if ($content->background_image)
                <div class="mb-3 flex items-center gap-4">
                    <img src="{{ Storage::url($content->background_image) }}" alt="" class="h-24 w-40 rounded-lg object-cover" />
                    <button type="button" wire:click="removeBackground" class="text-sm font-medium text-red-600 hover:text-red-700">Hapus gambar</button>
                </div>
            @endif
            <input type="file" wire:model="backgroundImage" accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream hover:file:bg-denah-terracotta-light" />
            @error('backgroundImage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="mt-4 flex justify-end">
            <button wire:click="saveBody" type="button"
                    class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">Simpan Penjelasan</button>
        </div>
    </div>

    {{-- ===== Form tahapan (create / edit) ===== --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm" x-data="{ lang: 'id' }">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-display text-base font-bold text-gray-900">{{ $editingId ? 'Edit Tahapan' : 'Tambah Tahapan' }}</h3>
            <x-admin.lang-tabs />
        </div>

        <form wire:submit="saveStep" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Judul (ID)</label>
                <input type="text" wire:model="titleId"
                       class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                @error('titleId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div x-show="lang === 'en'" x-cloak>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Title (EN)</label>
                <input type="text" wire:model="titleEn"
                       class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                @error('titleEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Deskripsi (ID)</label>
                <textarea wire:model="descriptionId" rows="3"
                          class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"></textarea>
                @error('descriptionId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div x-show="lang === 'en'" x-cloak>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Description (EN)</label>
                <textarea wire:model="descriptionEn" rows="3"
                          class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"></textarea>
                @error('descriptionEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Icon</label>
                <select wire:model="icon"
                        class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta">
                    @foreach ($icons as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('icon') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">{{ $editingId ? 'Perbarui' : 'Tambah' }}</button>
                @if ($editingId)
                    <button type="button" wire:click="resetForm"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                @endif
                        </div>
        </form>
    </div>

    {{-- ===== Daftar tahapan ===== --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-display text-base font-bold text-gray-900">Daftar Tahapan ({{ $steps->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="py-2 pr-3">Urut</th>
                        <th class="py-2 pr-3">Judul (ID)</th>
                        <th class="py-2 pr-3">Judul (EN)</th>
                        <th class="py-2 pr-3">Deskripsi</th>
                        <th class="py-2 pr-3">Icon</th>
                        <th class="py-2 pr-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($steps as $step)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap py-3 pr-3">
                                <div class="flex items-center gap-1 text-gray-400">
                                    <button wire:click="move({{ $step->id }}, 'up')" class="hover:text-gray-700">&uarr;</button>
                                    <button wire:click="move({{ $step->id }}, 'down')" class="hover:text-gray-700">&darr;</button>
                                </div>
                            </td>
                            <td class="py-3 pr-3 font-medium text-gray-800">{{ $step->title_id ?: $step->title }}</td>
                            <td class="py-3 pr-3 text-gray-500">{{ $step->title_en }}</td>
                            <td class="max-w-xs truncate py-3 pr-3 text-gray-500">{{ \Illuminate\Support\Str::limit($step->description_id ?: $step->description, 80) }}</td>
                            <td class="py-3 pr-3 text-gray-500">{{ $step->icon }}</td>
                            <td class="whitespace-nowrap py-3 pr-3">
                                <button wire:click="editStep({{ $step->id }})"
                                        class="text-xs font-semibold text-denah-terracotta hover:underline">Edit</button>
                                <button wire:click="confirmDelete('step', {{ $step->id }}, 'Hapus tahapan &quot;{{ $step->title_id ?: $step->title }}&quot;?')"
                                        class="ml-2 text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada tahapan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-admin.confirm-modal />
</div>


