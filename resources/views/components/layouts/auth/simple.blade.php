<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen antialiased" style="background-color: #2a2d35;">
        <div class="relative grid min-h-dvh lg:grid-cols-[1fr_1.1fr]">

            {{-- Panel izquierdo: Imagen de fondo (solo desktop) --}}
            <div class="relative hidden overflow-hidden lg:block">
                <img src="{{ asset('images/login.jpg') }}"
                     alt="Laboratorio Veterinario"
                     class="absolute inset-0 h-full w-full object-cover" />

                {{-- Overlay oscuro para integrar con el tema --}}
                <div class="absolute inset-0 bg-gradient-to-r from-neutral-950/10 to-neutral-950/30"></div>

                {{-- Dot-grid pattern sobre la imagen --}}
                <div class="login-dot-grid absolute inset-0 opacity-[0.04]"
                     style="background-image: radial-gradient(circle, #22d3ee 0.8px, transparent 0.8px);
                            background-size: 24px 24px;">
                </div>

                {{-- Línea decorativa superior --}}
                <div class="absolute top-0 right-0 left-0 h-[1px] z-10"
                     style="background: linear-gradient(90deg, transparent 0%, rgba(34,211,238,0.3) 50%, transparent 100%);"></div>

                {{-- Línea decorativa inferior --}}
                <div class="absolute right-0 bottom-0 left-0 h-[1px] z-10"
                     style="background: linear-gradient(90deg, transparent 0%, rgba(34,211,238,0.15) 50%, transparent 100%);"></div>
            </div>

            {{-- Panel derecho: Formulario --}}
            <div class="relative flex min-h-dvh flex-col items-center justify-center p-6 md:p-10 lg:min-h-0"
                 style="background: linear-gradient(180deg, #2a2d35 0%, #2d3039 100%);">

                {{-- Borde izquierdo sutil (solo desktop) --}}
                <div class="absolute top-0 bottom-0 left-0 hidden w-[1px] lg:block"
                     style="background: linear-gradient(180deg, transparent 0%, rgba(63,63,70,0.5) 30%, rgba(63,63,70,0.5) 70%, transparent 100%);"></div>

                <div class="flex w-full max-w-sm flex-col gap-2">
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>

        </div>
        @fluxScripts
    </body>
</html>
