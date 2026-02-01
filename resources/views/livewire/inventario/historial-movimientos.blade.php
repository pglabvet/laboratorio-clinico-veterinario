<div class="space-y-6">
    {{-- Encabezado --}}
    <div>
        <flux:heading size="xl" class="mb-1">Historial de Movimientos</flux:heading>
        <flux:subheading>Consulta completa de entradas, salidas y consumos de inventario</flux:subheading>
    </div>

    {{-- Estadísticas rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Movimientos</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ number_format($estadisticas['total_movimientos']) }}
                    </p>
                </div>
                <flux:icon.clipboard-document-list class="size-12 text-neutral-400" />
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Entradas este mes</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($estadisticas['entradas_mes_actual']) }}
                    </p>
                </div>
                <flux:icon.arrow-up-tray class="size-12 text-green-400" />
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Salidas este mes</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ number_format($estadisticas['salidas_mes_actual']) }}
                    </p>
                </div>
                <flux:icon.arrow-down-tray class="size-12 text-red-400" />
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg">Filtros</flux:heading>
            <flux:button 
                wire:click="limpiarFiltros"
                variant="ghost"
                icon="x-mark"
                size="sm"
            >
                Limpiar
            </flux:button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {{-- Búsqueda por insumo --}}
            <flux:input 
                wire:model.live.debounce.300ms="buscar"
                placeholder="Buscar insumo..."
                icon="magnifying-glass"
            />

            {{-- Filtro por sucursal --}}
            <flux:select 
                wire:model.live="filtroSucursal"
                placeholder="Todas las sucursales"
            >
                <option value="">Todas las sucursales</option>
                @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                @endforeach
            </flux:select>

            {{-- Filtro por tipo de movimiento --}}
            <flux:select 
                wire:model.live="filtroTipoMovimiento"
                placeholder="Todos los tipos"
            >
                <option value="">Todos los tipos</option>
                @foreach($tiposMovimiento as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>

            {{-- Filtro por motivo --}}
            <flux:select 
                wire:model.live="filtroMotivo"
                placeholder="Todos los motivos"
            >
                <option value="">Todos los motivos</option>
                @foreach($motivos as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>

            {{-- Fecha desde --}}
            <flux:input 
                wire:model.live="filtroFechaDesde"
                type="date"
                label="Desde"
            />

            {{-- Fecha hasta --}}
            <flux:input 
                wire:model.live="filtroFechaHasta"
                type="date"
                label="Hasta"
            />
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
                                <span>Fecha</span>
                                @if($sortBy === 'fecha')
                                    <flux:icon.chevron-down class="size-4 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" />
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Sucursal
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Insumo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Tipo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Cantidad
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Motivo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Usuario
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Observación
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
                                    {{ $movimiento->cantidad > 0 ? '+' : '' }}{{ number_format($movimiento->cantidad, 2) }} 
                                    {{ $movimiento->insumo->unidadMedida->abreviatura ?? '' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <flux:badge color="zinc" size="sm">
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
                            <td colspan="8" class="px-6 py-12 text-center">
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
