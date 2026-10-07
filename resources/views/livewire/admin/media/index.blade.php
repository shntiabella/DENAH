<div class="space-y-6">
    <x-admin.page-header title="Media Library" description="Semua file yang pernah diupload di seluruh modul." />

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-col gap-2 sm:flex-row">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari file..."
                       class="flex-1 rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                <select wire:model.live="filterType" class="rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta">
                    <option value="all">Semua Tipe</option>
                    <option value="image">Gambar</option>
                    <option value="geojson">GeoJSON / JSON</option>
                    <option value="document">Dokumen (PDF/CSV)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="file" wire:model="upload" class="hidden" id="media-upload" />
                <label for="media-upload" wire:loading.attr.remove="opacity-50"
                       class="cursor-pointer rounded-lg bg-denah-terracotta px-4 py-2.5 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">
                    <span wire:loading.remove wire:target="upload">Upload File</span>
                    <span wire:loading wire:target="upload">Mengunggah...</span>
                </label>
                <button wire:click="uploadFile" wire:loading.attr.remove="hidden"
                        class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700">Simpan</button>
            </div>
        </div>
        @error('upload') <p class="mb-3 text-xs text-red-600">{{ $message }}</p> @enderror

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @forelse ($media as $m)
                <div class="group relative overflow-hidden rounded-lg border border-gray-100">
                    @if (str_starts_with($m->mime_type ?? '', 'image/'))
                        <img src="{{ Storage::disk($m->disk)->url($m->path) }}" alt="{{ $m->name }}" class="aspect-square w-full object-cover" />
                    @else
                        <div class="grid aspect-square w-full place-items-center bg-gray-50 text-gray-400">
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    @endif
                    <div class="p-2">
                        <p class="truncate text-xs font-medium text-gray-700" title="{{ $m->name }}">{{ $m->name }}</p>
                        <p class="text-[10px] text-gray-400">{{ number_format($m->size / 1024, 1) }} KB · {{ $m->module ?? '-' }}</p>
                    </div>
                    <button wire:click="confirmDelete('media', {{ $m->id }}, 'Hapus file &quot;{{ $m->name }}&quot;?')"
                            class="absolute right-1.5 top-1.5 hidden rounded-full bg-white/90 p-1 text-red-500 shadow group-hover:block hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                    </button>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-sm text-gray-400">Belum ada file media.</div>
            @endforelse
        </div>
        <div class="mt-5">{{ $media->links() }}</div>
    </div>
    <x-admin.confirm-modal />
</div>
