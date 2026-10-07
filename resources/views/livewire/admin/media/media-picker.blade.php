<div>
    <div x-data x-show="$wire.open" x-cloak
         @keydown.escape.window="$wire.close()"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="$wire.close()"></div>
        <div class="relative flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-xl"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="font-display text-lg font-bold text-gray-900">Pilih dari Media Library</h3>
                    <p class="text-sm text-gray-400">References / gambar yang telah di-upload.</p>
                </div>
                <button type="button" @click="$wire.close()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-50 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-1 border-b border-gray-100 px-6 pt-4">
                <button type="button" wire:click="switchTab('gallery')"
                        class="rounded-t-lg px-4 py-2 text-sm font-semibold {{ $tab === 'gallery' ? 'border-b-2 border-denah-terracotta text-denah-terracotta' : 'text-gray-400 hover:text-gray-600' }}">
                    Galeri References
                </button>
                <button type="button" wire:click="switchTab('upload')"
                        class="rounded-t-lg px-4 py-2 text-sm font-semibold {{ $tab === 'upload' ? 'border-b-2 border-denah-terracotta text-denah-terracotta' : 'text-gray-400 hover:text-gray-600' }}">
                    Upload Gambar Baru
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                @if ($tab === 'gallery')
                    <input type="text" wire:model.live.debounce.250ms="search" placeholder="Cari gambar…"
                           class="mb-4 w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    <div class="grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-5">
                        @forelse ($media as $m)
                            <button type="button" wire:click="select({{ $m->id }})"
                                    class="group overflow-hidden rounded-lg border border-gray-100 text-left hover:border-denah-terracotta hover:shadow-sm">
                                <img src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->name }}" class="aspect-square w-full object-cover" loading="lazy" />
                                <p class="truncate px-2 py-1.5 text-[11px] font-medium text-gray-600 group-hover:text-denah-terracotta" title="{{ $m->name }}">{{ $m->name }}</p>
                            </button>
                        @empty
                            <p class="col-span-full py-10 text-center text-sm text-gray-400">Belum ada gambar di Media Library. Gunakan tab "Upload Gambar Baru".</p>
                        @endforelse
                    </div>
                @else
                    <input type="file" wire:model="upload" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-denah-terracotta file:px-4 file:py-2 file:text-sm file:font-semibold file:text-denah-cream hover:file:bg-denah-terracotta-light" />
                    @error('upload') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-3 text-xs text-gray-400">Gambar akan disimpan sekaligus ke Media Library (References) lalu langsung dipakai.</p>
                    <div class="mt-4">
                        <button type="button" wire:click="uploadAndSelect" @if (! $upload) disabled @endif
                                class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light disabled:opacity-50">
                            Upload &amp; Gunakan
                        </button>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 px-6 py-4">
                <button type="button" @click="$wire.close()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
            </div>
        </div>
    </div>
</div>