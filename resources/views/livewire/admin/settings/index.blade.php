<div class="space-y-6">
    <x-admin.page-header title="Pengaturan Umum" description="Nama website, tagline, logo, favicon, dan meta SEO default." />

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form wire:submit="save" class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Website</label>
                    <input type="text" wire:model="siteName" wire:model.blur="siteName"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('siteName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Tagline</label>
                    <input type="text" wire:model="tagline" wire:model.blur="tagline"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Meta Title (SEO)</label>
                <input type="text" wire:model="metaTitle" wire:model.blur="metaTitle"
                       class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                @error('metaTitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Meta Description (SEO)</label>
                <textarea wire:model="metaDescription" wire:model.blur="metaDescription" rows="2"
                          class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"></textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Logo</label>
                    @if ($logoPick)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($logoPick) }}" alt="logo" class="h-12 w-auto rounded border border-denah-terracotta/40" />
                            <span class="text-xs text-denah-terracotta">Dipilih dari Media Library</span>
                        </div>
                    @elseif ($logoPath)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($logoPath) }}" alt="logo" class="h-12 w-auto rounded border border-gray-100" />
                            <button type="button" wire:click="removeLogo" class="text-xs font-medium text-red-600 hover:text-red-700">Hapus</button>
                        </div>
                    @endif
                    <input type="file" wire:model="logo" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream" />
                    @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="button" @click="$dispatch('open-media-picker', { target: 'logoPick' })"
                            class="mt-2 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Pilih dari Media Library
                    </button>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Favicon</label>
                    @if ($faviconPick)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($faviconPick) }}" alt="favicon" class="h-8 w-8 rounded border border-denah-terracotta/40" />
                            <span class="text-xs text-denah-terracotta">Dipilih dari Media Library</span>
                        </div>
                    @elseif ($faviconPath)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($faviconPath) }}" alt="favicon" class="h-8 w-8 rounded border border-gray-100" />
                            <button type="button" wire:click="removeFavicon" class="text-xs font-medium text-red-600 hover:text-red-700">Hapus</button>
                        </div>
                    @endif
                    <input type="file" wire:model="favicon" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream" />
                    @error('favicon') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="button" @click="$dispatch('open-media-picker', { target: 'faviconPick' })"
                            class="mt-2 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Pilih dari Media Library
                    </button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Gambar Background Halaman Peta</label>
                    @if ($petaBackgroundPick)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($petaBackgroundPick) }}" alt="bg peta" class="h-16 w-28 rounded border border-denah-terracotta/40 object-cover" />
                            <span class="text-xs text-denah-terracotta">Dipilih dari Media Library</span>
                        </div>
                    @elseif ($petaBackgroundPath)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($petaBackgroundPath) }}" alt="bg peta" class="h-16 w-28 rounded border border-gray-100 object-cover" />
                            <button type="button" wire:click="removePetaBackground" class="text-xs font-medium text-red-600 hover:text-red-700">Hapus</button>
                        </div>
                    @endif
                    <input type="file" wire:model="petaBackground" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream" />
                    @error('petaBackground') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="button" @click="$dispatch('open-media-picker', { target: 'petaBackgroundPick' })"
                            class="mt-2 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Pilih dari Media Library
                    </button>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Gambar Background Halaman Statistik</label>
                    @if ($statistikBackgroundPick)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($statistikBackgroundPick) }}" alt="bg statistik" class="h-16 w-28 rounded border border-denah-terracotta/40 object-cover" />
                            <span class="text-xs text-denah-terracotta">Dipilih dari Media Library</span>
                        </div>
                    @elseif ($statistikBackgroundPath)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($statistikBackgroundPath) }}" alt="bg statistik" class="h-16 w-28 rounded border border-gray-100 object-cover" />
                            <button type="button" wire:click="removeStatistikBackground" class="text-xs font-medium text-red-600 hover:text-red-700">Hapus</button>
                        </div>
                    @endif
                    <input type="file" wire:model="statistikBackground" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream" />
                    @error('statistikBackground') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="button" @click="$dispatch('open-media-picker', { target: 'statistikBackgroundPick' })"
                            class="mt-2 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Pilih dari Media Library
                    </button>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-denah-terracotta px-5 py-2.5 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">Simpan Pengaturan</button>
            </div>
        </form>
    </div>

    @livewire('admin.media.media-picker', ['target' => 'logoPick', 'key' => 'logoPick'])
    @livewire('admin.media.media-picker', ['target' => 'faviconPick', 'key' => 'faviconPick'])
    @livewire('admin.media.media-picker', ['target' => 'petaBackgroundPick', 'key' => 'petaBackgroundPick'])
    @livewire('admin.media.media-picker', ['target' => 'statistikBackgroundPick', 'key' => 'statistikBackgroundPick'])
</div>
</div>
