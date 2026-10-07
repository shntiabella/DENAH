@php
    use App\Support\AdminNav;
    $nav = AdminNav::items();
    $current = request()->route()?->getName();
@endphp

<aside x-data
       x-show="window.Alpine && true"
       x-bind:class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-40 w-64 transform border-r border-white/10 bg-denah-dark text-denah-cream transition-transform duration-300 lg:translate-x-0">
    <div class="flex h-full flex-col">
        {{-- Brand --}}
        <div class="flex h-16 items-center gap-2.5 border-b border-white/10 px-5">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-denah-terracotta text-denah-cream">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.5-2.5v-14L9 6l6-3 5.5 2.5v14L15 18l-6 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 6v14M15 3v14"/></svg>
            </span>
            <div class="leading-tight">
                <div class="font-display text-base font-extrabold tracking-tight">DENAH</div>
                <div class="text-[10px] tracking-wide text-denah-cream-muted">Admin Panel</div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @foreach ($nav as $item)
                @php $isActive = $current === $item['route'] || (isset($item['match']) && in_array($current, $item['match'])); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ $isActive ? 'bg-denah-terracotta text-denah-cream' : 'text-denah-cream-muted hover:bg-white/5 hover:text-denah-cream' }}">
                    <span class="h-5 w-5 shrink-0">{!! $item['icon'] !!}</span>
                    <span>{{ __($item['label']) }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Footer --}}
        <div class="border-t border-white/10 px-4 py-3">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 text-xs text-denah-cream-muted hover:text-denah-terracotta-light">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Lihat Website
            </a>
        </div>
    </div>
</aside>

{{-- Overlay mobile --}}
<div x-data x-show="$store.sidebar.open" x-cloak @click="$store.sidebar.open = false"
     class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>
