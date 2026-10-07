<div class="space-y-6">
    <x-admin.page-header title="Tentang DENAH" description="Edit teks, gambar ilustrasi, dan tombol Selengkapnya." />

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm" x-data="{ lang: 'id' }">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-display text-base font-bold text-gray-900">Konten Section</h3>
            <x-admin.lang-tabs />
        </div>
        <form wire:submit="save" class="space-y-5">
            {{-- Body ID --}}
            <div x-show="lang === 'id'">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Konten (rich text) — Bahasa Indonesia</label>
                <textarea wire:model="bodyId" rows="6" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta">{{ $bodyId }}</textarea>
                @error('bodyId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Body EN --}}
            <div x-show="lang === 'en'" x-cloak>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Content (rich text) — English</label>
                <textarea wire:model="bodyEn" rows="6" class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta">{{ $bodyEn }}</textarea>
                @error('bodyEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div x-show="lang === 'id'">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Teks Tombol "Selengkapnya" (ID)</label>
                    <input type="text" wire:model="moreLinkTextId" wire:model.blur="moreLinkTextId"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('moreLinkTextId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div x-show="lang === 'en'" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">"Read More" Button Text (EN)</label>
                    <input type="text" wire:model="moreLinkTextEn" wire:model.blur="moreLinkTextEn"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('moreLinkTextEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Link Tombol</label>
                    <input type="text" wire:model="moreLinkUrl" wire:model.blur="moreLinkUrl"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('moreLinkUrl') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Gambar / Ilustrasi</label>
                @if ($about->image)
                    <div class="mb-3 flex items-center gap-4">
                        <img src="{{ Storage::url($about->image) }}" alt="" class="h-24 w-40 rounded-lg object-cover" />
                        <button type="button" wire:click="removeImage" class="text-sm font-medium text-red-600 hover:text-red-700">Hapus gambar</button>
                    </div>
                @endif
                <input type="file" wire:model="image" accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream hover:file:bg-denah-terracotta-light" />
                @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Gambar Background Halaman</label>
                @if ($about->background_image)
                    <div class="mb-3 flex items-center gap-4">
                        <img src="{{ Storage::url($about->background_image) }}" alt="" class="h-24 w-40 rounded-lg object-cover" />
                        <button type="button" wire:click="removeBackground" class="text-sm font-medium text-red-600 hover:text-red-700">Hapus gambar</button>
                    </div>
                @endif
                <input type="file" wire:model="backgroundImage" accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream hover:file:bg-denah-terracotta-light" />
                @error('backgroundImage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-denah-terracotta px-5 py-2.5 text-sm font-semibold text-denah-cream shadow-sm transition hover:bg-denah-terracotta-light hover:text-denah-dark">
                    <svg class="h-4 w-4" wire:loading.remove wire:target="save" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <svg class="h-4 w-4 animate-spin" wire:loading wire:target="save" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
