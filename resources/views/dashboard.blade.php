<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Título del Dashboard --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Dashboard</h1>
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

        @can('ver-dashboard')
        {{-- Filtros Dashboard --}}
        <livewire:dashboard.filtros-dashboard />
        @endcan

        @can('ver-dashboard')
        {{-- Estadísticas Principales --}}
        <livewire:dashboard.estadisticas-principales />
        @endcan

        @can('ver-dashboard')
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
