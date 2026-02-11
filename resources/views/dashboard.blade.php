<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Título del Dashboard --}}
    <div class="flex items-center justify-between">
            <div>
                @php
                    $hora = now()->hour;
                    if ($hora >= 5 && $hora < 12) {
                        $saludo = 'Buenos días';
                        $icono = '☀️';
                    } elseif ($hora >= 12 && $hora < 19) {
                        $saludo = 'Buenas tardes';
                        $icono = '🌤️';
                    } else {
                        $saludo = 'Buenas noches';
                        $icono = '🌙';
                    }
                    $nombre = explode(' ', auth()->user()->name)[0];
                @endphp
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $saludo }}, {{ $nombre }} <span class="inline-block animate-pulse">{{ $icono }}</span></h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    @can('ver-estadisticas-completas')
                    Panel de control administrativo
                    @elsecan('ver-graficos-estadisticas')
                    Panel de control bioquímico
                    @else
                    Panel de control general
                    @endcan
                </p>
            </div>
        </div>

        {{-- Mensaje de bienvenida si no hay componentes visibles --}}
        @cannot('ver-estadisticas-completas')
        @cannot('ver-graficos-estadisticas')
        @cannot('ver-acciones-rapidas')
        @cannot('ver-ultimas-muestras')
        @cannot('ver-actividad-reciente')
        @cannot('ver-filtros-dashboard')
            <div class="rounded-lg border border-zinc-200 bg-white p-8 text-center dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                    <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-zinc-900 dark:text-white">Bienvenido al Sistema</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Tu cuenta ha sido creada exitosamente. Por favor, contacta al administrador para que te asigne los permisos necesarios.
                </p>
            </div>
        @endcannot
        @endcannot
        @endcannot
        @endcannot
        @endcannot
        @endcannot

        @can('ver-filtros-dashboard')
        {{-- Filtros Dashboard --}}
        <livewire:dashboard.filtros-dashboard />
        @endcan

        @can('ver-dashboard')
        {{-- Estadísticas Principales --}}
        <livewire:dashboard.estadisticas-principales />
        @endcan

        @can('ver-acciones-rapidas')
        {{-- Acciones Rápidas --}}
        <livewire:dashboard.acciones-rapidas />
        @endcan

        @can('ver-graficos-estadisticas')
        {{-- Gráficos Estadísticas --}}
        <livewire:dashboard.graficos-estadisticas />
        @endcan

        @can('ver-ultimas-muestras')
        {{-- Últimas Muestras (Vista Bioquímico) --}}
        <livewire:dashboard.ultimas-muestras />
        @endcan

        @can('ver-actividad-reciente')
        {{-- Actividad Reciente --}}
        <livewire:dashboard.actividad-reciente />
        @endcan
    </div>
</x-layouts.app>
