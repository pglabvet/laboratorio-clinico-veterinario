<x-layouts.app title="Registrar Entrada de Insumos">
    @can('ver-registrar-entrada')
        @livewire('insumos.registrar-entrada-insumos')
    @endcan
</x-layouts.app>
