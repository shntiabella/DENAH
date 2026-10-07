{{-- Modal konfirmasi global sebelum aksi destruktif.
     Dipakai via Livewire: $this->dispatch('confirm-delete', id: $model->id, message: '...') --}}
<div x-data="{
        show: false,
        id: null,
        message: '',
        confirm() {
            this.show = false;
            this.$wire.$dispatch('confirmed-delete', { id: this.id });
        }
     }"
     @confirm-delete.window="show = true; id = $event.detail.id; message = $event.detail.message ?? 'Hapus data ini?'"
     @keydown.escape.window="show = false"
     class="relative z-50"
     wire:key="confirm-modal">
    <div x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

    <div x-show="show" x-cloak class="fixed inset-0 flex items-center justify-center p-4">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-red-50 text-red-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                </span>
                <div class="flex-1">
                    <h3 class="font-display text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="message"></p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="show = false"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="button" @click="confirm"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Hapus</button>
            </div>
        </div>
    </div>
</div>
