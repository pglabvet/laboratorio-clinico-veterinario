<div class="min-h-screen bg-gray-50 dark:bg-zinc-800">
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <flux:heading size="xl">Revisar Análisis</flux:heading>
            <flux:subheading>Aprueba o rechaza análisis finalizados</flux:subheading>
        </div>

        {{-- Filtros y búsqueda --}}
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Búsqueda --}}
                <div class="md:col-span-2">
                    <flux:input 
                        wire:model.live.debounce.300ms="busqueda"
                        placeholder="Buscar por código, paciente o propietario..."
                        icon="magnifying-glass"
                    />
                </div>

                {{-- Filtro por estado --}}
                <div>
                    <flux:select wire:model.live="filtroEstado" placeholder="Filtrar por estado">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En proceso</option>
                        <option value="finalizado">Finalizado</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                    </flux:select>
                </div>

                {{-- Filtro por tipo --}}
                <div>
                    <flux:select wire:model.live="filtroTipoAnalisis" placeholder="Tipo de análisis">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposAnalisis as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
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

                {{-- Botón limpiar --}}
                <div class="flex items-end">
                    <flux:button wire:click="limpiarFiltros" variant="ghost" icon="arrow-path">
                        Limpiar filtros
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- Tabla de análisis --}}
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="ordenar('id')" class="flex items-center gap-1 font-semibold text-gray-700 dark:text-zinc-300 hover:text-gray-900 dark:hover:text-zinc-100">
                                    ID
                                    @if($ordenarPor === 'id')
                                        <i class="fas fa-sort-{{ $ordenDireccion === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-zinc-300">Código Muestra</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-zinc-300">Paciente / Propietario</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-zinc-300">Tipo Análisis</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-zinc-300">Bioquímico</th>
                            <th class="px-4 py-3 text-left">
                                <button wire:click="ordenar('fecha_finalizacion')" class="flex items-center gap-1 font-semibold text-gray-700 dark:text-zinc-300 hover:text-gray-900 dark:hover:text-zinc-100">
                                    Fecha
                                    @if($ordenarPor === 'fecha_finalizacion')
                                        <i class="fas fa-sort-{{ $ordenDireccion === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-zinc-300">Estado</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-zinc-300">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                        @forelse($analisis as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-zinc-100">
                                #{{ $item->id }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $item->muestra->codigo_muestra }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900 dark:text-zinc-100">
                                        {{ $item->muestra->paciente_nombre }}
                                    </div>
                                    <div class="text-gray-500 dark:text-zinc-400">
                                        {{ $item->muestra->propietario_nombre }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-zinc-100">
                                {{ $item->tipoAnalisis->nombre }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-zinc-100">
                                {{ $item->bioquimico->name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-zinc-100">
                                {{ $item->fecha_finalizacion?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
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
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Ver/Editar --}}
                                    <flux:button 
                                        href="{{ route('analisis.capturar-resultados', $item->id) }}"
                                        size="sm"
                                        variant="ghost"
                                        title="Ver y editar resultados"
                                    >
                                        Ver
                                    </flux:button>

                                    @if($item->estado === 'finalizado')
                                        <flux:button 
                                            wire:click="aprobarAnalisis({{ $item->id }})"
                                            wire:confirm="¿Está seguro de aprobar este análisis?"
                                            size="sm"
                                            variant="primary"
                                            icon="check"
                                            title="Aprobar"
                                        />

                                        {{-- Rechazar --}}
                                        <flux:button 
                                            wire:click="rechazarAnalisis({{ $item->id }})"
                                            wire:confirm="¿Está seguro de rechazar este análisis?"
                                            size="sm"
                                            variant="danger"
                                            icon="x-mark"
                                            title="Rechazar"
                                        />
                                    @elseif($item->estado === 'rechazado')
                                        {{-- Editar resultados rechazados --}}
                                        <flux:button 
                                            href="{{ route('resultados.editar', $item->id) }}"
                                            size="sm"
                                            variant="outline"
                                            icon="pencil"
                                            title="Corregir resultados"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-zinc-400">
                                <i class="fas fa-inbox text-4xl mb-3"></i>
                                <p>No se encontraron análisis con los filtros seleccionados</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($analisis->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-zinc-700">
                {{ $analisis->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
