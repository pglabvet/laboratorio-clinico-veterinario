<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('success')" />
    <x-toast type="error" :message="session('error')" />
    <x-toast type="warning" :message="session('warning')" />

    {{-- Header de la página --}}
    <div class="mb-4">
        <flux:heading size="xl" class="mb-1">Revisar Análisis</flux:heading>
        <flux:subheading>Aprueba o rechaza análisis finalizados</flux:subheading>
    </div>

    {{-- Bloque de filtros --}}
    <div class="mb-4" x-data="{ 
        mostrarFiltros: window.innerWidth >= 640,
        get filtrosActivos() {
            let count = 0;
            if ($wire.busqueda) count++;
            if ($wire.filtroFechaDesde) count++;
            if ($wire.filtroFechaHasta) count++;
            if ($wire.filtroEstado) count++;
            if ($wire.filtroTipoAnalisis) count++;
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
            
            {{-- Contenido de filtros --}}
            <div class="space-y-3">
                {{-- Búsqueda (ocupa todo el ancho) --}}
                <div>
                    <flux:input 
                        wire:model.live.debounce.300ms="busqueda"
                        icon="magnifying-glass"
                        placeholder="Buscar por código, paciente o propietario..."
                        class="w-full"
                    />
                </div>

                {{-- Grid de filtros: 2 columnas en móvil, flex en desktop --}}
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

                    {{-- Dropdown de filtros rápidos de fecha --}}
                    <div>
                        <flux:dropdown>
                            <flux:button variant="outline" icon="calendar-days" icon-trailing="chevron-down" class="w-full justify-between">
                                {{ $filtroPeriodo ?: 'Período' }}
                            </flux:button>

                            <flux:menu>
                                <flux:menu.item wire:click="filtrarHoy" icon="sun">
                                    Hoy
                                </flux:menu.item>
                                <flux:menu.item wire:click="filtrarAyer" icon="arrow-uturn-left">
                                    Ayer
                                </flux:menu.item>
                                <flux:menu.item wire:click="filtrarUltimos7Dias" icon="calendar">
                                    Últimos 7 días
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item wire:click="filtrarEstaSemana" icon="calendar-days">
                                    Esta semana
                                </flux:menu.item>
                                <flux:menu.item wire:click="filtrarEsteMes" icon="calendar-days">
                                    Este mes
                                </flux:menu.item>
                                <flux:menu.item wire:click="filtrarAnioActual" icon="calendar-days">
                                    Año actual
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro por estado --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="funnel" icon-trailing="chevron-down">
                                {{ $filtroEstado ? $filtroEstado : 'Estado' }}
                            </flux:button>

                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroEstado', '')" icon="bars-3">
                                    Todos los estados
                                </flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item wire:click="$set('filtroEstado', 'Pendiente')" icon="clock">
                                    Pendiente
                                </flux:menu.item>
                                <flux:menu.item wire:click="$set('filtroEstado', 'En revision')" icon="magnifying-glass">
                                    En revisión
                                </flux:menu.item>
                                <flux:menu.item wire:click="$set('filtroEstado', 'Aprobado')" icon="check-circle">
                                    Aprobado
                                </flux:menu.item>
                                <flux:menu.item wire:click="$set('filtroEstado', 'Enviado')" icon="paper-airplane">
                                    Enviado
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro por tipo de análisis --}}
                    <div class="w-full sm:w-auto">
                        <flux:dropdown>
                            <flux:button variant="outline" icon="beaker" icon-trailing="chevron-down">
                                {{ $filtroTipoAnalisis ? Str::limit($tiposAnalisis->firstWhere('id', $filtroTipoAnalisis)?->nombre, 12) : 'Tipo análisis' }}
                            </flux:button>

                            <flux:menu>
                                <flux:menu.item wire:click="$set('filtroTipoAnalisis', '')" icon="bars-3">
                                    Todos los tipos
                                </flux:menu.item>
                                <flux:menu.separator />
                                @foreach($tiposAnalisis as $tipo)
                                    <flux:menu.item wire:click="$set('filtroTipoAnalisis', '{{ $tipo->id }}')" icon="document-text">
                                        {{ $tipo->nombre }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    </div>

                    {{-- Filtro por sucursales --}}
                <div class="w-full sm:w-auto">
                    <flux:dropdown>
                        <flux:button variant="outline" icon="building-office" icon-trailing="chevron-down">
                            {{ $filtroSucursal ? $sucursales->firstWhere('id', $filtroSucursal)?->nombre : 'Sucursal' }}
                        </flux:button>

                        <flux:menu>
                            <flux:menu.item wire:click="$set('filtroSucursal', '')" icon="bars-3">
                                Todas las sucursales
                            </flux:menu.item>
                            <flux:menu.separator />
                            @foreach($sucursales as $sucursal)
                                <flux:menu.item wire:click="$set('filtroSucursal', '{{ $sucursal->id }}')" icon="building-office-2">
                                    {{ $sucursal->nombre }}
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

    {{-- Tabla de análisis --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-md ring-1 ring-neutral-200/50 dark:border-neutral-700 dark:bg-neutral-800 dark:ring-neutral-700/50">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenar('id')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>ID</span>
                                @if($ordenarPor === 'id')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($ordenDireccion === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Código Muestra
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Paciente / Propietario
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Tipo Análisis
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Sucursal
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            <button wire:click="ordenar('fecha_finalizacion')" class="flex items-center gap-1 hover:text-neutral-900 dark:hover:text-neutral-100">
                                <span>Fecha</span>
                                @if($ordenarPor === 'fecha_finalizacion')
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        @if($ordenDireccion === 'asc')
                                            <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        @else
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($analisis as $item)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50" wire:key="analisis-{{ $item->id }}">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                #{{ $item->id }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="font-semibold text-cyan-600 dark:text-cyan-400">
                                    {{ $item->muestra->codigo_muestra }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $item->muestra->paciente_nombre }}
                                </div>
                                <div class="text-neutral-500 dark:text-neutral-400">
                                    {{ $item->muestra->propietario_nombre }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $item->tipoAnalisis->nombre }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $item->muestra->sucursal->nombre ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                @if($item->fecha_finalizacion)
                                    <div class="flex flex-col">
                                        <span>{{ $item->fecha_finalizacion->format('d/m/Y') }}</span>
                                        <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $item->fecha_finalizacion->format('H:i:s') }}</span>
                                    </div>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @php
                                    $estadoConfig = [
                                        'Pendiente' => ['color' => 'amber', 'texto' => 'Pendiente'],
                                        'En revision' => ['color' => 'blue', 'texto' => 'En revisión'],
                                        'Aprobado' => ['color' => 'green', 'texto' => 'Aprobado'],
                                        'Enviado' => ['color' => 'purple', 'texto' => 'Enviado'],
                                    ];
                                    $config = $estadoConfig[$item->estado] ?? ['color' => 'zinc', 'texto' => $item->estado];
                                @endphp
                                <flux:badge :color="$config['color']" size="sm">
                                    {{ $config['texto'] }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    {{-- Botón ver --}}
                                    <flux:button
                                        href="{{ route('analisis.capturar-resultados', $item->id) }}"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        color="neutral"
                                        title="Ver y editar resultados"
                                    />

                                    @if($item->estado === 'En revision')
                                        {{-- Botón aprobar --}}
                                        <flux:button
                                            wire:click="confirmarAprobar({{ $item->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="check"
                                            color="green"
                                            title="Aprobar"
                                        />

                                        {{-- Botón rechazar --}}
                                        <flux:button
                                            wire:click="confirmarRechazar({{ $item->id }})"
                                            variant="ghost"
                                            size="sm"
                                            icon="x-mark"
                                            color="red"
                                            title="Rechazar"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.clipboard-document-list class="mb-3 h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                                    <flux:heading size="lg" class="mb-1">No se encontraron análisis</flux:heading>
                                    <flux:subheading>
                                        @if ($busqueda || $filtroEstado || $filtroTipoAnalisis || $filtroFechaDesde || $filtroFechaHasta)
                                            No se encontraron análisis con los filtros seleccionados
                                        @else
                                            No hay análisis para revisar en este momento
                                        @endif
                                    </flux:subheading>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($analisis->hasPages())
            <div class="border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900">
                {{ $analisis->links() }}
            </div>
        @endif
    </div>

    {{-- Modal de confirmación para aprobar --}}
    <flux:modal wire:model="modalAprobar" class="w-full max-w-md">
        <div class="space-y-6">
            {{-- Ícono de éxito --}}
            <div class="flex justify-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Título y mensaje --}}
            <div class="text-center">
                <flux:heading size="lg" class="mb-2">Aprobar Análisis</flux:heading>
                <flux:subheading>
                    ¿Está seguro de que desea aprobar este análisis? Una vez aprobado, estará disponible para su envío.
                </flux:subheading>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cancelarAprobar"
                    variant="ghost"
                >
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button"
                    wire:click="aprobarAnalisis"
                    variant="primary"
                    icon="check"
                >
                    Aprobar
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal de confirmación para rechazar --}}
    <flux:modal wire:model="modalRechazar" class="w-full max-w-md">
        <div class="space-y-6">
            {{-- Ícono de advertencia --}}
            <div class="flex justify-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
            </div>

            {{-- Título y mensaje --}}
            <div class="text-center">
                <flux:heading size="lg" class="mb-2">Rechazar Análisis</flux:heading>
                <flux:subheading>
                    ¿Está seguro de que desea rechazar este análisis? El bioquímico deberá realizar las correcciones necesarias.
                </flux:subheading>
            </div>

            {{-- Campo de observaciones --}}
            <div>
                <flux:textarea 
                    wire:model="observacionesRechazo"
                    label="Observaciones (opcional)"
                    placeholder="Indique las correcciones necesarias..."
                    rows="4"
                />
            </div>

            {{-- Botones --}}
            <div class="flex justify-end gap-3">
                <flux:button 
                    type="button"
                    wire:click="cancelarRechazar"
                    variant="ghost"
                >
                    Cancelar
                </flux:button>
                <flux:button 
                    type="button"
                    wire:click="rechazarAnalisis"
                    variant="danger"
                    icon="x-mark"
                >
                    Rechazar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
