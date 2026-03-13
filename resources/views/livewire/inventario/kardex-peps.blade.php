<div class="space-y-6">
    {{-- Encabezado --}}
    <div>
        <div class="flex items-center gap-4 mb-2">
            <flux:heading size="xl">Kardex PEPS</flux:heading>
        </div>
        <flux:subheading>Reporte valorizado del inventario por método PEPS (Primero en Entrar, Primero en Salir)</flux:subheading>
    </div>

    {{-- Mensajes --}}
    <x-toast type="danger" :message="session('error')" />

    {{-- Filtros --}}
    <div class="mb-2" x-data="{
        mostrarFiltros: window.innerWidth >= 640,
        get filtrosActivos() {
            let count = 0;
            if ($wire.sucursal_id) count++;
            if ($wire.filtro_categoria) count++;
            if ($wire.insumo_id) count++;
            if ($wire.fecha_desde) count++;
            if ($wire.fecha_hasta) count++;
            return count;
        }
    }">
        {{-- Toggle móvil --}}
        <div class="mb-3 sm:hidden">
            <flux:button @click="mostrarFiltros = !mostrarFiltros" variant="outline" icon="funnel" class="w-full relative">
                <span x-text="mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros'"></span>
                <span x-show="filtrosActivos > 0" class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-blue-600 rounded-full" x-text="filtrosActivos"></span>
            </flux:button>
        </div>

        <div x-show="mostrarFiltros"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="rounded-lg border border-neutral-200 bg-neutral-50 p-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

            <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-row sm:flex-wrap sm:items-end">
                {{-- Sucursal --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="building-office-2" icon-trailing="chevron-down">
                            {{ $sucursal_id ? $this->sucursales->firstWhere('id', $sucursal_id)?->nombre : 'Sucursal' }}
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item wire:click="$set('sucursal_id', '')" icon="bars-3">Todas las sucursales</flux:menu.item>
                            <flux:menu.separator />
                            @foreach($this->sucursales as $sucursal)
                                <flux:menu.item wire:click="$set('sucursal_id', '{{ $sucursal->id }}')" icon="building-storefront">{{ $sucursal->nombre }}</flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                </div>

                {{-- Categoría --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="tag" icon-trailing="chevron-down">
                            {{ $filtro_categoria ? $this->categorias->firstWhere('id', $filtro_categoria)?->nombre : 'Categoría' }}
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item wire:click="$set('filtro_categoria', '')" icon="bars-3">Todas las categorías</flux:menu.item>
                            <flux:menu.separator />
                            @foreach($this->categorias as $categoria)
                                <flux:menu.item wire:click="$set('filtro_categoria', '{{ $categoria->id }}')" icon="tag">{{ $categoria->nombre }}</flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                </div>

                {{-- Insumo --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="beaker" icon-trailing="chevron-down">
                            {{ $insumo_id ? $this->insumos->firstWhere('id', $insumo_id)?->nombre : 'Insumo' }}
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item wire:click="$set('insumo_id', '')" icon="bars-3">Todos los insumos</flux:menu.item>
                            <flux:menu.separator />
                            @foreach($this->insumos as $insumo)
                                <flux:menu.item wire:click="$set('insumo_id', '{{ $insumo->id }}')" icon="beaker">{{ $insumo->nombre }}</flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                </div>

                {{-- Fecha desde --}}
                <div>
                    <flux:input type="date" wire:model.live="fecha_desde" label="Desde" />
                </div>

                {{-- Fecha hasta --}}
                <div>
                    <flux:input type="date" wire:model.live="fecha_hasta" label="Hasta" />
                </div>

                {{-- Limpiar --}}
                <div class="w-full sm:w-auto">
                    <flux:button wire:click="limpiarFiltros" variant="outline" icon="x-mark">
                        Limpiar
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla Kardex --}}
    @if($kardexData)
        <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700">
            <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">
                            {{ $this->tituloKardex }}
                        </flux:heading>
                        <flux:subheading>
                            {{ $this->sucursales->firstWhere('id', $sucursal_id)?->nombre }}
                            @if($fecha_desde || $fecha_hasta)
                                — {{ $fecha_desde ? \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') : 'Inicio' }} 
                                al {{ $fecha_hasta ? \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') : 'Hoy' }}
                            @endif
                        </flux:subheading>
                    </div>
                    <div class="text-right flex flex-col items-end gap-3">
                        <div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Saldo Final</p>
                            <p class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                                {{ number_format($kardexData['saldo_final_cantidad'], 2) }} uds
                            </p>
                            <p class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                Bs {{ number_format($kardexData['saldo_final_costo'], 2) }}
                            </p>
                        </div>
                        {{-- Botones de exportación --}}
                        <div class="flex gap-2">
                            <a 
                                href="{{ $this->urlExcel }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg border border-emerald-500 text-emerald-700 dark:text-emerald-400 dark:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Excel
                            </a>
                            <a 
                                href="{{ $this->urlPdf }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg border border-red-400 text-red-700 dark:text-red-400 dark:border-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-900">
                        <tr>
                            <th rowspan="2" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300 border-b border-neutral-200 dark:border-neutral-700">
                                Fecha
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300 border-b border-neutral-200 dark:border-neutral-700">
                                Detalle
                            </th>
                            <th rowspan="2" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300 border-b border-neutral-200 dark:border-neutral-700">
                                Insumo
                            </th>
                            <th colspan="4" class="px-4 py-2 text-center text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 border-b border-neutral-200 dark:border-neutral-700 bg-emerald-50 dark:bg-emerald-900/20">
                                Cantidades
                            </th>
                            <th colspan="4" class="px-4 py-2 text-center text-xs font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 border-b border-neutral-200 dark:border-neutral-700 bg-blue-50 dark:bg-blue-900/20">
                                Costos (Bs)
                            </th>
                        </tr>
                        <tr>
                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-400 bg-emerald-50/50 dark:bg-emerald-900/10">
                                Inicio
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-green-700 dark:text-green-400 bg-emerald-50/50 dark:bg-emerald-900/10">
                                Entrada
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-red-700 dark:text-red-400 bg-emerald-50/50 dark:bg-emerald-900/10">
                                Salida
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300 bg-emerald-50/50 dark:bg-emerald-900/10 font-bold">
                                Saldo
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-neutral-600 dark:text-neutral-400 bg-blue-50/50 dark:bg-blue-900/10">
                                Inicio
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-green-700 dark:text-green-400 bg-blue-50/50 dark:bg-blue-900/10">
                                Entrada
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-red-700 dark:text-red-400 bg-blue-50/50 dark:bg-blue-900/10">
                                Salida
                            </th>
                            <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300 bg-blue-50/50 dark:bg-blue-900/10 font-bold">
                                Saldo
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                        @forelse($this->registrosPaginados as $registro)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                                {{-- Fecha --}}
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-900 dark:text-neutral-100">
                                    {{ $registro['fecha'] }}
                                </td>
                                {{-- Detalle --}}
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-900 dark:text-neutral-100">
                                    {{ $registro['detalle'] }}
                                </td>
                                {{-- Insumo --}}
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-900 dark:text-neutral-100">
                                    {{ $registro['insumo_nombre'] ?? '' }}
                                </td>

                                {{-- CANTIDADES --}}
                                <td class="whitespace-nowrap px-3 py-3 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ number_format($registro['inicio_cantidad'], 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-center text-sm">
                                    @if($registro['entrada_cantidad'] !== null)
                                        <span class="font-medium text-green-600 dark:text-green-400">
                                            {{ number_format($registro['entrada_cantidad'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-neutral-300 dark:text-neutral-600">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-center text-sm">
                                    @if($registro['salida_cantidad'] !== null)
                                        <span class="font-medium text-red-600 dark:text-red-400">
                                            {{ number_format($registro['salida_cantidad'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-neutral-300 dark:text-neutral-600">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-center text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                    {{ number_format($registro['saldo_cantidad'], 2) }}
                                </td>

                                {{-- COSTOS --}}
                                <td class="whitespace-nowrap px-3 py-3 text-center text-sm text-neutral-500 dark:text-neutral-400 bg-blue-50/30 dark:bg-blue-900/5">
                                    {{ number_format($registro['inicio_costo'], 2) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-center text-sm bg-blue-50/30 dark:bg-blue-900/5">
                                    @if($registro['entrada_costo'] !== null)
                                        <span class="font-medium text-green-600 dark:text-green-400">
                                            {{ number_format($registro['entrada_costo'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-neutral-300 dark:text-neutral-600">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-center text-sm bg-blue-50/30 dark:bg-blue-900/5">
                                    @if($registro['salida_costo'] !== null)
                                        <span class="font-medium text-red-600 dark:text-red-400">
                                            {{ number_format($registro['salida_costo'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-neutral-300 dark:text-neutral-600">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-center text-sm font-bold text-blue-700 dark:text-blue-300 bg-blue-50/30 dark:bg-blue-900/5">
                                    {{ number_format($registro['saldo_costo'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-neutral-500 dark:text-neutral-400">
                                        <flux:icon.document-text class="mb-3 size-12" />
                                        <p class="text-sm">No hay movimientos registrados para este período</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- Pie de tabla con totales --}}
                    @if($this->totalRegistros > 0)
                    <tfoot>
                        <tr class="bg-neutral-100 dark:bg-neutral-700">
                            <td colspan="3" class="px-4 py-3 text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                Totales Finales
                            </td>
                            <td colspan="3"></td>
                            <td class="px-3 py-3 text-center text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                {{ number_format($kardexData['saldo_final_cantidad'], 2) }}
                            </td>
                            <td colspan="3"></td>
                            <td class="px-3 py-3 text-center text-sm font-bold text-blue-700 dark:text-blue-300 bg-blue-100/50 dark:bg-blue-900/20">
                                Bs {{ number_format($kardexData['saldo_final_costo'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            {{-- Paginación --}}
            @if($this->totalPaginas > 1)
                <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            Mostrando 
                            <span class="font-medium">{{ (($paginaActual - 1) * $porPagina) + 1 }}</span>
                            a 
                            <span class="font-medium">{{ min($paginaActual * $porPagina, $this->totalRegistros) }}</span>
                            de 
                            <span class="font-medium">{{ $this->totalRegistros }}</span>
                            movimientos
                        </p>
                        
                        <div class="flex items-center gap-2">
                            <flux:button 
                                wire:click="paginaAnterior"
                                variant="outline"
                                size="sm"
                                icon="chevron-left"
                                :disabled="$paginaActual <= 1"
                            >
                                Anterior
                            </flux:button>

                            @php
                                $totalPags = $this->totalPaginas;
                                $current = $paginaActual;
                                $pages = [];
                                
                                if ($totalPags <= 7) {
                                    $pages = range(1, $totalPags);
                                } else {
                                    $pages = [1];
                                    if ($current > 3) $pages[] = '...';
                                    for ($i = max(2, $current - 1); $i <= min($totalPags - 1, $current + 1); $i++) {
                                        $pages[] = $i;
                                    }
                                    if ($current < $totalPags - 2) $pages[] = '...';
                                    $pages[] = $totalPags;
                                }
                            @endphp

                            @foreach($pages as $page)
                                @if($page === '...')
                                    <span class="px-2 text-neutral-400 dark:text-neutral-500">…</span>
                                @else
                                    <flux:button 
                                        wire:click="irAPagina({{ $page }})"
                                        variant="{{ $page == $paginaActual ? 'primary' : 'outline' }}"
                                        size="sm"
                                    >
                                        {{ $page }}
                                    </flux:button>
                                @endif
                            @endforeach

                            <flux:button 
                                wire:click="paginaSiguiente"
                                variant="outline"
                                size="sm"
                                icon-trailing="chevron-right"
                                :disabled="$paginaActual >= $this->totalPaginas"
                            >
                                Siguiente
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @elseif($sucursal_id)
        <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <div class="flex flex-col items-center justify-center text-neutral-500 dark:text-neutral-400">
                <flux:icon.document-text class="mb-3 size-16" />
                <p class="text-lg font-medium">Sin datos disponibles</p>
                <p class="text-sm mt-1">No se encontraron movimientos para estos filtros</p>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-neutral-900 rounded-lg border border-neutral-200 dark:border-neutral-700 p-12 text-center">
            <div class="flex flex-col items-center justify-center text-neutral-500 dark:text-neutral-400">
                <flux:icon.funnel class="mb-3 size-16" />
                <p class="text-lg font-medium">Seleccione una sucursal</p>
                <p class="text-sm mt-1">Escoja una sucursal para generar el Kardex PEPS. Opcionalmente filtre por categoría o insumo específico</p>
            </div>
        </div>
    @endif
</div>
