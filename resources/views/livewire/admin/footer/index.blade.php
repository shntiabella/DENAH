<div class="space-y-6">
    <x-admin.page-header title="Footer & Kontak" description="Edit deskripsi footer, kontak, dan link sosial media." />

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Footer & kontak --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-display text-base font-bold text-gray-900">Kontak & Deskripsi Footer</h3>
            <form wire:submit="saveContact" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Deskripsi Footer</label>
                    <textarea wire:model="footerDescription" wire:model.blur="footerDescription" rows="3"
                              class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta"></textarea>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model="contactEmail" wire:model.blur="contactEmail"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                    @error('contactEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Telepon</label>
                    <input type="text" wire:model="contactPhone" wire:model.blur="contactPhone"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Alamat</label>
                    <input type="text" wire:model="contactAddress" wire:model.blur="contactAddress"
                           class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm outline-none focus:border-denah-terracotta focus:ring-1 focus:ring-denah-terracotta" />
                </div>
                <button type="submit" class="rounded-lg bg-denah-terracotta px-4 py-2 text-sm font-semibold text-denah-cream hover:bg-denah-terracotta-light">Simpan</button>
            </form>
        </div>

        {{-- Sosial media --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-display text-base font-bold text-gray-900">Link Sosial Media</h3>
            <ul class="space-y-2">
                @foreach ($socials as $s)
                    <li class="rounded-lg border border-gray-100 px-3 py-2">
                        @if ($editingSocialId === $s->id)
                            <div class="flex flex-wrap items-center gap-2">
                                <input wire:model="editPlatform" placeholder="Platform" class="rounded border border-gray-200 px-2 py-1 text-sm" />
                                <input wire:model="editIcon" placeholder="icon" class="w-20 rounded border border-gray-200 px-2 py-1 text-sm" />
                                <input wire:model="editUrl" placeholder="https://" class="flex-1 rounded border border-gray-200 px-2 py-1 text-sm" />
                                <button wire:click="updateSocial" class="text-xs font-semibold text-denah-terracotta">Simpan</button>
                                <button wire:click="cancelEditSocial" class="text-xs text-gray-400">Batal</button>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="grid h-7 w-7 place-items-center rounded bg-gray-100 text-xs font-semibold text-gray-600">{{ strtoupper(substr($s->icon, 0, 2)) }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-800">{{ $s->platform }}</div>
                                    <div class="truncate text-xs text-gray-400">{{ $s->url }}</div>
                                </div>
                                <button wire:click="editSocial({{ $s->id }})" class="text-xs font-semibold text-denah-terracotta hover:underline">Edit</button>
                                <button wire:click="confirmDelete('social', {{ $s->id }}, 'Hapus sosial media &quot;{{ $s->platform }}&quot;?')" class="text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
            <div class="mt-4 grid grid-cols-[1fr_80px_1fr] gap-2">
                <input wire:model="newPlatform" placeholder="Platform (Facebook)" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                <input wire:model="newIcon" placeholder="icon" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" />
                <input wire:model="newUrl" placeholder="https://" class="rounded-lg border border-gray-200 px-3 py-2 text-sm" />
            </div>
            <div class="mt-2 flex justify-end">
                <button wire:click="addSocial" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Tambah</button>
            </div>
            @error('newUrl') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
    <x-admin.confirm-modal />
</div>
