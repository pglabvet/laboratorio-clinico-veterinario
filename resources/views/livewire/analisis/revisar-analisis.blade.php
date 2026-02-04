<div>
    {{-- Mensajes toast en esquina superior derecha --}}
    <x-toast type="success" :message="session('success')" />
    <x-toast type="error" :message="session('error')" />
    <x-toast type="warning" :message="session('warning')" />

    {{-- Header de la página --}}
    <div class="mb-6">
        <flux:heading size="xl" class="mb-2">Revisar Análisis</flux:heading>
        <flux:subheading>Aprueba o rechaza análisis finalizados</flux:subheading>
    </div>

    {{-- Barra de filtros --}}
    <div class="mb-6 space-y-4">
        {{-- Fila 1: Búsqueda y filtros principales --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            {{-- Búsqueda --}}
            <div class="w-full sm:flex-1">
                <flux:input 
                    wire:model.live.debounce.300ms="busqueda"
                    icon="magnifying-glass"
                    placeholder="Buscar por código, paciente o propietario..."
                    class="w-full"
                />
            </div>

            {{-- Filtro por estado --}}
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="filtroEstado" placeholder="Filtrado">
                    <option value="">Todos los tipos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En proceso</option>
                    <option value="finalizado">Finalizado</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                </flux:select>
            </div>

            {{-- Filtro por tipo --}}
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="filtroTipoAnalisis" placeholder="Todos los tipos">
                    <option value="">Todos los tipos</option>
                    @foreach($tiposAnalisis as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        {{-- Fila 2: Filtros de fecha con botones rápidos --}}
        <div class="flex flex-col gap-4">
            {{-- Botones de filtros rápidos --}}
            <div class="flex flex-wrap gap-2">
                <flux:button 
                    wire:click="filtrarHoy"
                    variant="ghost" 
                    size="sm"
                >
                    Hoy
                </flux:button>
                <flux:button 
                    wire:click="filtrarAyer"
                    variant="ghost" 
                    size="sm"
                >
                    Ayer
                </flux:button>
                <flux:button 
                    wire:click="filtrarUltimos7Dias"
                    variant="ghost" 
                    size="sm"
                >
                    Últimos 7 días
                </flux:button>
                <flux:button 
                    wire:click="filtrarEstaSemana"
                    variant="ghost" 
                    size="sm"
                >
                    Esta semana
                </flux:button>
                <flux:button 
                    wire:click="filtrarEsteMes"
                    variant="ghost" 
                    size="sm"
                >
                    Este mes
                </flux:button>
                <flux:button 
                    wire:click="filtrarAnioActual"
                    variant="ghost" 
                    size="sm"
                >
                    Año actual
                </flux:button>
            </div>

            {{-- Inputs de fecha manual --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- Fecha desde --}}
                <div class="w-full sm:w-auto">
                    <flux:input 
                        type="date"
                        wire:model.live="filtroFechaDesde"
                        label="Desde"
                    />
                </div>

                {{-- Fecha hasta --}}
                <div class="w-full sm:w-auto">
                    <flux:input 
                        type="date"
                        wire:model.live="filtroFechaHasta"
                        label="Hasta"
                    />
                </div>

                {{-- Botón limpiar filtros --}}
                <flux:button 
                    wire:click="limpiarFiltros" 
                    variant="ghost" 
                    icon="arrow-path"
                >
                    Limpiar filtros
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Tabla de análisis --}}
    <div class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow dark:border-neutral-700 dark:bg-neutral-800">
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
                            Bioquímico
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
                                {{ $item->bioquimico->name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $item->fecha_finalizacion?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @php
                                    $estadoConfig = [
                                        'pendiente' => ['color' => 'zinc', 'texto' => 'Pendiente'],
                                        'en_proceso' => ['color' => 'blue', 'texto' => 'En Proceso'],
                                        'finalizado' => ['color' => 'yellow', 'texto' => 'Finalizado'],
                                        'aprobado' => ['color' => 'green', 'texto' => 'Aprobado'],
                                        'rechazado' => ['color' => 'red', 'texto' => 'Rechazado'],
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

                                    @if($item->estado === 'finalizado')
                                        {{-- Botón aprobar --}}
                                        <flux:button
                                            wire:click="aprobarAnalisis({{ $item->id }})"
                                            wire:confirm="¿Está seguro de aprobar este análisis?"
                                            variant="ghost"
                                            size="sm"
                                            icon="check"
                                            color="green"
                                            title="Aprobar"
                                        />

                                        {{-- Botón rechazar --}}
                                        <flux:button
                                            wire:click="rechazarAnalisis({{ $item->id }})"
                                            wire:confirm="¿Está seguro de rechazar este análisis?"
                                            variant="ghost"
                                            size="sm"
                                            icon="x-mark"
                                            color="red"
                                            title="Rechazar"
                                        />
                                    @elseif($item->estado === 'rechazado')
                                        {{-- Botón editar --}}
                                        <flux:button
                                            href="{{ route('resultados.editar', $item->id) }}"
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil"
                                            color="cyan"
                                            title="Corregir resultados"
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
</div>
