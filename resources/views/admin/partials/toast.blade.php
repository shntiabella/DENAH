{{-- Toast notification container (Livewire flash) --}}
<div x-data="{
        toasts: [],
        add(type, message) {
            const id = Date.now();
            this.toasts.push({ id, type, message });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
     }"
     @notify.window="add($event.detail.type, $event.detail.message)"
     @flash.window="add($event.detail.type, $event.detail.message)"
     class="fixed bottom-5 right-5 z-[60] flex w-full max-w-sm flex-col gap-2"
     wire:key="toast-container">

    @if (session('success'))
        <script>window.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('flash', { detail: { type: 'success', message: @json(session('success')) } })))</script>
    @endif
    @if (session('error'))
        <script>window.addEventListener('DOMContentLoaded', () => window.dispatchEvent(new CustomEvent('flash', { detail: { type: 'error', message: @json(session('error')) } })))</script>
    @endif

    <template x-for="t in toasts" :key="t.id">
        <div x-show="true"
             :class="{
                'border-green-200 bg-green-50 text-green-800': t.type === 'success',
                'border-red-200 bg-red-50 text-red-800': t.type === 'error',
                'border-amber-200 bg-amber-50 text-amber-800': t.type === 'warning',
                'border-gray-200 bg-gray-50 text-gray-800': t.type === 'info'
             }"
             class="flex items-start gap-3 rounded-xl border px-4 py-3 shadow-lg"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <span x-html="t.type === 'success' ? '&#10003;' : (t.type === 'error' ? '&#10007;' : '&#9432;')" class="mt-0.5 text-sm font-bold"></span>
            <p class="flex-1 text-sm font-medium" x-text="t.message"></p>
            <button type="button" @click="remove(t.id)" class="text-current opacity-60 hover:opacity-100">&times;</button>
        </div>
    </template>
</div>

