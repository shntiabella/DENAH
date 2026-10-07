@props(['class' => ''])
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    {{-- Pin lokasi dengan rumah di dalamnya — mark brand DENAH --}}
    <path d="M12 21.5s7-5.6 7-10.75A7 7 0 1 0 5 10.75C5 15.9 12 21.5 12 21.5Z"
          stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M12 6.7 8.5 9.8v3.45c0 .36.29.65.65.65h5.7c.36 0 .65-.29.65-.65V9.8L12 6.7Z" fill="currentColor" />
</svg>
