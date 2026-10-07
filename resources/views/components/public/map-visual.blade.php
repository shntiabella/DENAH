@props(['variant' => 'light', 'showControls' => true, 'class' => ''])
@php
    $dark = $variant === 'dark';
    $controlBtn = $dark
        ? 'border-white/10 bg-black/40 text-denah-cream backdrop-blur-md hover:bg-black/60'
        : 'border-denah-ink/10 bg-white/90 text-denah-ink hover:bg-white';
@endphp

<div {{ $attributes->merge(['class' => 'relative w-full overflow-hidden rounded-2xl border ' . ($dark ? 'border-white/10 bg-denah-dark' : 'border-denah-ink/10 bg-white') . ' ' . $class]) }}>
    <!-- subtle grid di dalam kartu peta -->
    <div class="absolute inset-0 {{ $dark ? 'bg-geo-grid opacity-60' : 'bg-geo-grid-light' }}"></div>

    <!-- SVG map (dark: coklat-teal ala dark map GIS / light: krem hangat) -->
    <svg class="relative h-full w-full" viewBox="0 0 640 420" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <!-- water band -->
        <path d="M0 300c60-40 120-20 180-60s120-30 160-60 60-20 90-50 100-20 150 10 60 60 60 60v220H0z"
              class="{{ $dark ? 'fill-[#1E2C31]' : 'fill-[#E3E9E6]' }}" />
        <!-- land polygons -->
        <g class="{{ $dark ? 'fill-[#2A2119]' : 'fill-[#EFE7D8]' }}">
            <path d="M60 90l70-30 90 25-20 80-80 35-60-50z" />
            <path d="M240 180l90-45 80 30-30 90-75 20-65-40z" />
            <path d="M420 120l70-25 90 20-25 85-80 25-55-45z" />
        </g>

        <!-- batas administrasi (garis dashed) -->
        <g class="{{ $dark ? 'stroke-denah-cream/25' : 'stroke-denah-ink/20' }}" stroke-width="1.5" stroke-dasharray="7 6">
            <path d="M225 30v360" />
            <path d="M405 30v360" />
        </g>

        <!-- settlement clusters (permukiman — terracotta) -->
        <g fill="currentColor" class="text-denah-terracotta">
            <circle cx="150" cy="150" r="12" opacity="0.85" />
            <circle cx="330" cy="230" r="16" opacity="0.95" />
            <circle cx="470" cy="175" r="11" opacity="0.85" />
            <circle cx="215" cy="115" r="8" opacity="0.75" />
            <circle cx="560" cy="120" r="9" opacity="0.8" />
            <circle cx="90" cy="250" r="7" opacity="0.7" />
        </g>

        <!-- focused marker pin -->
        <g class="{{ $dark ? 'text-denah-terracotta-light' : 'text-denah-terracotta' }}">
            <path d="M330 190c-11 0-19 7-19 16 0 12 19 28 19 28s19-16 19-28c0-9-8-16-19-16z" fill="currentColor" opacity="0.95" />
            <circle cx="330" cy="206" r="4" class="{{ $dark ? 'fill-denah-dark' : 'fill-white' }}" />
        </g>

        <!-- coordinate label hint -->
        <text x="18" y="40" class="font-mono text-[11px] tracking-widest {{ $dark ? 'fill-denah-cream-muted' : 'fill-denah-ink-muted' }}" opacity="0.7">7&#176;56'15"S | 112&#176;37'35"E</text>
    </svg>

    @if ($showControls)
        <!-- zoom controls -->
        <div class="absolute right-3 top-3 flex flex-col gap-1 opacity-90">
            <button type="button" class="grid h-8 w-8 place-items-center rounded-lg border shadow-sm transition {{ $controlBtn }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            </button>
            <button type="button" class="grid h-8 w-8 place-items-center rounded-lg border shadow-sm transition {{ $controlBtn }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" d="M5 12h14"/></svg>
            </button>
        </div>

        <!-- legend chip -->
        <div class="absolute bottom-3 left-3 flex items-center gap-2 rounded-full border px-3 py-1.5 shadow-sm {{ $dark ? 'border-white/10 bg-black/40 backdrop-blur-md' : 'border-denah-ink/10 bg-white/90' }}">
            <span class="h-2.5 w-2.5 rounded-full bg-denah-terracotta"></span>
            <span class="text-xs font-medium {{ $dark ? 'text-denah-cream' : 'text-denah-ink' }}">Permukiman</span>
        </div>
    @endif
</div>
