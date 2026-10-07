<div class="space-y-6">
    <x-admin.page-header title="Kelola Hero Beranda" description="Edit konten section hero pada halaman beranda publik." />

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Form hero --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2" x-data="{ lang: 'id' }">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-display text-base font-bold text-gray-900">Konten Hero</h3>
                <x-admin.lang-tabs />
            </div>
            <form wire:submit="saveHero" class="space-y-5">
                {{-- Badge Text --}}
                <div x-show="lang === 'id'">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Badge Text — Bahasa Indonesia</label>
                    <input type="text" wire:model="badgeTextId"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('badgeTextId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div x-show="lang === 'en'" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Badge Text — English</label>
                    <input type="text" wire:model="badgeTextEn"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('badgeTextEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Headline --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div x-show="lang === 'id'">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Headline Baris 1 (ID)</label>
                        <input type="text" wire:model="headline1Id"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('headline1Id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="lang === 'en'" x-cloak>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Headline Line 1 (EN)</label>
                        <input type="text" wire:model="headline1En"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('headline1En') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="lang === 'id'">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Headline Baris 2 (ID) <span class="text-denah-terracotta">(aksen)</span></label>
                        <input type="text" wire:model="headline2Id"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('headline2Id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="lang === 'en'" x-cloak>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Headline Line 2 (EN) <span class="text-denah-terracotta">(accent)</span></label>
                        <input type="text" wire:model="headline2En"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('headline2En') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Subheadline --}}
                <div x-show="lang === 'id'">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Subheadline (ID)</label>
                    <textarea wire:model="subheadlineId" rows="3"
                              class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"></textarea>
                    @error('subheadlineId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div x-show="lang === 'en'" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Subheadline (EN)</label>
                    <textarea wire:model="subheadlineEn" rows="3"
                              class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"></textarea>
                    @error('subheadlineEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- CTA --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div x-show="lang === 'id'">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Teks Tombol CTA Utama (ID)</label>
                        <input type="text" wire:model="ctaPrimaryTextId"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('ctaPrimaryTextId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="lang === 'en'" x-cloak>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Primary CTA Text (EN)</label>
                        <input type="text" wire:model="ctaPrimaryTextEn"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('ctaPrimaryTextEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="lang === 'id'">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Teks Tombol CTA Sekunder (ID)</label>
                        <input type="text" wire:model="ctaSecondaryTextId"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('ctaSecondaryTextId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-show="lang === 'en'" x-cloak>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Secondary CTA Text (EN)</label>
                        <input type="text" wire:model="ctaSecondaryTextEn"
                               class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                        @error('ctaSecondaryTextEn') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Koordinat</label>
                    <input type="text" wire:model="coordinates"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none transition focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('coordinates') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Gambar Background</label>
                    @if ($backgroundImagePick)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ Storage::url($backgroundImagePick) }}" alt="" class="h-24 w-40 rounded-lg object-cover" />
                            <span class="text-xs text-denah-terracotta">Dipilih dari Media Library</span>
                        </div>
                    @elseif ($hero->background_image)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ Storage::url($hero->background_image) }}" alt="" class="h-24 w-40 rounded-lg object-cover" />
                            <button type="button" wire:click="removeBackground" class="text-sm font-medium text-red-600 hover:text-red-700">Hapus gambar</button>
                        </div>
                    @endif
                    <input type="file" wire:model="backgroundImage" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream hover:file:bg-denah-terracotta-light" />
                    @error('backgroundImage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <button type="button" @click="$dispatch('open-media-picker', { target: 'backgroundImagePick' })"
                            class="mt-2 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Pilih dari Media Library
                    </button>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-denah-terracotta px-5 py-2.5 text-sm font-semibold text-denah-cream shadow-sm transition hover:bg-denah-terracotta-light hover:text-denah-dark">
                        <svg class="h-4 w-4" wire:loading.remove wire:target="saveHero" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <svg class="h-4 w-4 animate-spin" wire:loading wire:target="saveHero" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        Simpan Hero
                    </button>
                </div>
            </form>
        </div>

        {{-- Badge kecil --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="font-display text-base font-bold text-gray-900">Badge Kecil</h3>
            <p class="mt-1 text-sm text-gray-400">Badge fitur di hero (Spasial Data, Klasifikasi, Open Access).</p>

            <ul class="mt-4 space-y-2">
                @foreach ($badges as $badge)
                    <li class="flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2">
                        @if ($editingBadgeId === $badge->id)
                            <input type="text" wire:model="editingBadgeLabel" wire:keydown.enter="updateBadge"
                                   class="flex-1 rounded-md border border-denah-terracotta px-2 py-1 text-sm outline-none" />
                            <button wire:click="updateBadge" class="text-xs font-semibold text-denah-terracotta">Simpan</button>
                            <button wire:click="cancelEditBadge" class="text-xs text-gray-400">Batal</button>
                        @else
                            <span class="flex-1 text-sm text-gray-700">{{ $badge->label }}</span>
                            <button wire:click="moveBadge({{ $badge->id }}, 'up')" class="text-gray-400 hover:text-gray-700">&uarr;</button>
                            <button wire:click="moveBadge({{ $badge->id }}, 'down')" class="text-gray-400 hover:text-gray-700">&darr;</button>
                            <button wire:click="editBadge({{ $badge->id }})" class="text-xs text-denah-terracotta hover:underline">Edit</button>
                            <button wire:click="confirmDelete('badge', {{ $badge->id }}, 'Hapus badge &quot;{{ $badge->label }}&quot;?')" class="text-xs text-red-500 hover:underline">Hapus</button>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-4 flex gap-2">
                <input type="text" wire:model="newBadge" wire:keydown.enter="addBadge" placeholder="Tambah badge baru"
                       class="flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-denah-terracotta" />
                <button wire:click="addBadge" class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Tambah</button>
            </div>
            @error('newBadge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <x-admin.confirm-modal />

    @livewire('admin.media.media-picker', ['target' => 'backgroundImagePick', 'key' => 'heroBgPick'])
</div>


