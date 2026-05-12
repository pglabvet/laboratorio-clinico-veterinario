<div class="space-y-6">
    {{-- Encabezado --}}
    <div>
        <flux:heading size="xl" class="mb-1">Historial de Movimientos</flux:heading>
        <flux:subheading>Consulta completa de entradas, salidas y consumos de inventario</flux:subheading>
    </div>

    {{-- Estadísticas rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Total Movimientos --}}
        <div class="group block rounded-xl border border-zinc-200 bg-white p-6 transition-all hover:border-cyan-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-cyan-700">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                        Total Movimientos
                    </flux:heading>
                    <div class="mt-2 flex items-baseline gap-2">
                        <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ number_format($estadisticas['total_movimientos']) }}
                        </flux:heading>
                    </div>
                    <flux:subheading class="mt-1 text-xs">
                        Registros totales
                    </flux:subheading>
                </div>
                <div class="rounded-lg bg-cyan-100 p-3 transition-transform group-hover:scale-110 dark:bg-cyan-900/20">
                    <flux:icon.clipboard-document-list class="size-6 text-cyan-600 dark:text-cyan-400" />
                </div>
            </div>
        </div>

        {{-- Entradas este mes --}}
        <div class="group block rounded-xl border border-zinc-200 bg-white p-6 transition-all hover:border-green-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-green-700">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                        Entradas este mes
                    </flux:heading>
                    <div class="mt-2 flex items-baseline gap-2">
                        <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ number_format($estadisticas['entradas_mes_actual']) }}
                        </flux:heading>
                    </div>
                    <flux:subheading class="mt-1 text-xs">
                        Ingresos al inventario
                    </flux:subheading>
                </div>
                <div class="rounded-lg bg-green-100 p-3 transition-transform group-hover:scale-110 dark:bg-green-900/20">
                    <flux:icon.arrow-up-tray class="size-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        {{-- Salidas este mes --}}
        <div class="group block rounded-xl border border-zinc-200 bg-white p-6 transition-all hover:border-red-300 hover:shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-red-700">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400 font-medium">
                        Salidas este mes
                    </flux:heading>
                    <div class="mt-2 flex items-baseline gap-2">
                        <flux:heading size="xl" class="text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ number_format($estadisticas['salidas_mes_actual']) }}
                        </flux:heading>
                    </div>
                    <flux:subheading class="mt-1 text-xs">
                        <span class="text-red-600 dark:text-red-400">Salidas del inventario</span>
                    </flux:subheading>
                </div>
                <div class="rounded-lg bg-red-100 p-3 transition-transform group-hover:scale-110 dark:bg-red-900/20">
                    <flux:icon.arrow-down-tray class="size-6 text-red-600 dark:text-red-400" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="mb-4" x-data="{
        mostrarFiltros: window.innerWidth >= 640,
        get filtrosActivos() {
            let count = 0;
            if ($wire.buscar) count++;
            if ($wire.filtroSucursal) count++;
            if ($wire.filtroTipoMovimiento) count++;
            if ($wire.filtroMotivo) count++;
            if ($wire.filtroFechaDesde) count++;
            if ($wire.filtroFechaHasta) count++;
            return count;
        }
    }">
        {{-- Botón para mostrar/ocultar filtros en móvil --}}
        <div class="mb-3 sm:hidden">
            <flux:button
                @click="mostrarFiltros = !mostrarFiltros"
                variant="outline"
                icon="funnel"
                class="w-full relative"
            >
                <span x-text="mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros'"></span>
                <span x-show="filtrosActivos > 0"
                      class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-blue-600 rounded-full"
                      x-text="filtrosActivos"></span>
            </flux:button>
        </div>

        {{-- Contenedor de filtros --}}
        <div x-show="mostrarFiltros"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="rounded-lg border border-neutral-200 bg-neutral-50 p-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

            <div class="space-y-3">
                {{-- Búsqueda (ancho completo) --}}
                <div>
                    <flux:input
                        wire:model.live.debounce.300ms="buscar"
                        icon="magnifying-glass"
                        placeholder="Buscar insumo..."
                        class="w-full"
                    />
                </div>

                {{-- Dropdowns + Fechas + Limpiar --}}
                <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-row sm:flex-wrap sm:items-end">
                    {{-- Fecha desde --}}
                    <div>
                        <flux:input
                            type="date"
                            wire:model.live="filtroFechaDesde"
                            label="Desde"
                        />
                    </div>

                    {{-- Fecha hasta --}}
                    <div>
                        <flux:input
                            type="date"
                            wire:model.live="filtroFechaHasta"
                            label="Hasta"
                        />
                    </div>

                    {{-- Filtro por sucursal --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="building-office-2" icon-trailing="chevron-down">
                                {{ $filtroSucursal ? $this->sucursales->firstWhere('id', $filtroSucursal)?->nombre : 'Sucursal' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroSucursal', '')" icon="bars-3">
                                    Todas las sucursales
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach($this->sucursales as $sucursal)
                                    <flux:menu.item wire:click="$set('filtroSucursal', '{{ $sucursal->id }}')" icon="building-storefront">
                                        {{ $sucursal->nombre }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro por tipo de movimiento --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="arrows-up-down" icon-trailing="chevron-down">
                                {{ $filtroTipoMovimiento ? ($tiposMovimiento[$filtroTipoMovimiento] ?? 'Tipo') : 'Tipo' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroTipoMovimiento', '')" icon="bars-3">
                                    Todos los tipos
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach($tiposMovimiento as $key => $label)
                                    <flux:menu.item wire:click="$set('filtroTipoMovimiento', '{{ $key }}')" icon="tag">
                                        {{ $label }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro por motivo --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="document-text" icon-trailing="chevron-down">
                                {{ $filtroMotivo ? ($motivos[$filtroMotivo] ?? 'Motivo') : 'Motivo' }}
                            </flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroMotivo', '')" icon="bars-3">
                                    Todos los motivos
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach($motivos as $key => $label)
                                    <flux:menu.item wire:click="$set('filtroMotivo', '{{ $key }}')" icon="document-text">
                                        {{ $label }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Botón limpiar filtros --}}
                    <div class="w-full sm:w-auto">
                        <flux:button
                            wire:click="limpiarFiltros"
                            variant="outline"
                            icon="x-mark"
                        >
                            Limpiar
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Tabla de movimientos --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenarPor('fecha')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>FECHA</span>
                                @if($sortBy === 'fecha')
                                    <flux:icon.chevron-down class="size-4 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            SUCURSAL
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            INSUMO
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            TIPO
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            CANTIDAD
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            COSTO UNIT.
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            COSTO TOTAL
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            MOTIVO
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            USUARIO
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            OBSERVACIÓN
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse($movimientos as $movimiento)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                <div>
                                    <div class="font-medium">{{ $movimiento->fecha->format('d/m/Y') }}</div>
                                    <div class="text-xs text-neutral-500">{{ $movimiento->fecha->format('H:i') }}</div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $movimiento->sucursal->nombre }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $movimiento->insumo->nombre }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge 
                                    :color="match($movimiento->tipo_movimiento) {
                                        'ENTRADA' => 'green',
                                        'SALIDA_MANUAL' => 'red',
                                        'CONSUMO_ANALISIS' => 'blue',
                                        'AJUSTE' => 'orange',
                                        default => 'zinc'
                                    }"
                                    size="sm"
                                >
                                    {{ $tiposMovimiento[$movimiento->tipo_movimiento] ?? $movimiento->tipo_movimiento }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="font-medium {{ $movimiento->cantidad < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    {{ $movimiento->cantidad > 0 ? '+' : '' }}{{ \App\Helpers\FormatoHelper::dinamico($movimiento->cantidad) }} 
                                    {{ $movimiento->insumo->unidadMedida->abreviatura ?? '' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                @if($movimiento->costo_unitario > 0)
                                    Bs {{ \App\Helpers\FormatoHelper::dinamico($movimiento->costo_unitario) }}
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                @if($movimiento->costo_total > 0)
                                    Bs {{ \App\Helpers\FormatoHelper::dinamico($movimiento->costo_total) }}
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge 
                                    :color="match($movimiento->motivo) {
                                        'COMPRA' => 'green',
                                        'DEVOLUCION' => 'blue',
                                        'AJUSTE_INVENTARIO' => 'amber',
                                        'MERMA' => 'red',
                                        'VENCIMIENTO' => 'red',
                                        'USO_EXTRAORDINARIO' => 'orange',
                                        'CONSUMO_ANALISIS' => 'sky',
                                        'OTRO' => 'zinc',
                                        default => 'zinc'
                                    }"
                                    size="sm"
                                >
                                    {{ $motivos[$movimiento->motivo] ?? $movimiento->motivo }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $movimiento->usuario->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400 max-w-xs truncate" title="{{ $movimiento->observacion }}">
                                {{ $movimiento->observacion ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-neutral-500 dark:text-neutral-400">
                                    <flux:icon.clipboard-document-list class="mb-3 size-12" />
                                    <p class="text-sm">No hay movimientos registrados con los filtros seleccionados</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($movimientos->hasPages())
            <div class="border-t border-neutral-200 dark:border-neutral-700 px-6 py-4">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>
</div>
