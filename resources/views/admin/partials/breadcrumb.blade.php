@php
    $crumbs = $breadcrumbs ?? [];
@endphp
@if (! empty($crumbs))
<nav class="mb-4" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm">
        @foreach ($crumbs as $i => $crumb)
            @if (! $loop->first)
                <li class="text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></li>
            @endif
            <li>
                @if (! empty($crumb['url']) && ! ($loop->last))
                    <a href="{{ $crumb['url'] }}" wire:navigate class="text-gray-500 hover:text-denah-terracotta">{{ __($crumb['label']) }}</a>
                @else
                    <span class="font-semibold text-gray-700">{{ __($crumb['label']) }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
