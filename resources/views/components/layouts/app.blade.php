<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            body.app-bg {
                background-image: url('{{ asset('images/fondo_pdf_v3.png') }}');
                background-repeat: repeat;
                background-size: auto;
                background-attachment: fixed;
            }
            .dark body.app-bg {
                background-image: url('{{ asset('images/fondo_sistema_oscuro.png') }}');
            }

            /* Dark mode: overlay to soften the background pattern */
            .dark .app-bg-overlay {
                position: fixed;
                inset: 0;
                background: rgba(39, 39, 42, 0.65);
                pointer-events: none;
                z-index: 0;
            }

            /* Ensure content sits above the overlay */
            .dark body.app-bg > *:not(.app-bg-overlay) {
                position: relative;
                z-index: 1;
            }

            /* Header needs higher z-index so notification dropdowns appear above page content */
            .dark body.app-bg > header,
            .dark body.app-bg > [data-flux-header] {
                z-index: 10;
            }

            /* Ensure Livewire navigate progress bar is visible above the sticky header */
            #nprogress {
                z-index: 50 !important;
            }
            #nprogress .bar {
                z-index: 50 !important;
            }
        </style>
    </head>
    <body class="min-h-screen bg-gray-100 dark:bg-zinc-800 app-bg">
        {{-- Dark mode background overlay --}}
        <div class="app-bg-overlay"></div>
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
