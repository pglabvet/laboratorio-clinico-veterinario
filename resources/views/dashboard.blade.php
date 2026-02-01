<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Título del Dashboard --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    @role('Administrador')
                    Panel de control administrativo
                    @else
                    Panel de control bioquímico
                    @endrole
                </p>
            </div>
        </div>

        @role('Bioquímico')
        {{-- Escaneo Rápido --}}
        <livewire:muestras.escaneo-rapido />
        @endrole

        {{-- Filtros Dashboard --}}
        <livewire:dashboard.filtros-dashboard />

        {{-- Estadísticas Principales --}}
        <livewire:dashboard.estadisticas-principales />

        {{-- Gráficos Estadísticas --}}
        <livewire:dashboard.graficos-estadisticas />

        {{-- Actividad Reciente --}}
        <livewire:dashboard.actividad-reciente />
    </div>
</x-layouts.app>
