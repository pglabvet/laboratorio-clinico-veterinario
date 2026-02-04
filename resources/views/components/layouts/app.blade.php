<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        {{-- Sidebar --}}
        <x-layouts.app.sidebar />

        {{-- Header/Topbar --}}
        <x-layouts.app.header />

        {{-- Page Content --}}
        <flux:main>
            {{ $slot }}
        </flux:main>

        @fluxScripts
        @stack('scripts')
    </body>
</html>
