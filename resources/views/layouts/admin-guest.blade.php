<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'DENAH CMS') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased bg-denah-dark text-denah-cream">
        <div class="pointer-events-none absolute inset-0 bg-denah-dots opacity-20"></div>
        <div class="pointer-events-none absolute -left-40 top-10 h-96 w-96 rounded-full bg-denah-terracotta/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-40 bottom-10 h-96 w-96 rounded-full bg-denah-terracotta-light/10 blur-3xl"></div>
        <div class="relative flex min-h-full items-center justify-center px-4 py-10">
            {{ $slot }}
        </div>
    </body>
</html>
