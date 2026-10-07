<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? __($title) : config('app.name', 'DENAH') }}</title>

        <meta name="description" content="{{ $description ?? __('DENAH — Digital Ecosystem & Navigation for Area Housing. Eksplorasi informasi spasial mengenai persebaran dan klasifikasi permukiman melalui peta interaktif.') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preconnect" href="https://images.unsplash.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts / Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @stack('head')
    </head>
    <body class="font-sans text-denah-ink-muted bg-white overflow-x-hidden">
        <x-public.navbar />

        <main>
            {{ $slot }}
        </main>

        <x-public.footer />

        @livewireScripts
        @stack('scripts')
    </body>
</html>