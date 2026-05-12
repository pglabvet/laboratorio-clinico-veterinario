<!-- Propiedades de Campos Etiquetados -->
<div class="space-y-4">
    <!-- Títulos alternativos -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Títulos Alternativos</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">El bioquímico podrá elegir uno de estos títulos al capturar resultados</p>
        <div class="space-y-2 max-h-40 overflow-y-auto">
            @foreach($props['titulos'] ?? [] as $tIndex => $titulo)
            <div class="flex gap-2">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulos.{{ $tIndex }}"
                    placeholder="Ej: UROCULTIVO, HEMOCULTIVO..."
                    class="flex-1 px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                @if(count($props['titulos'] ?? []) > 1)
                <button 
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.titulos', {{ json_encode(array_values(array_filter($props['titulos'], fn($f, $i) => $i !== $tIndex, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs font-medium">
                    <i class="fas fa-trash"></i>
                </button>
                @endif
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.titulos', {{ json_encode(array_merge($props['titulos'] ?? [], [''])) }})"
            variant="outline" 
            icon="plus" 
            class="w-full mt-2"
            size="sm">
            Agregar Título
        </flux:button>
    </div>

    <!-- Columnas (encabezados) -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Columnas</label>
        <div class="space-y-2">
            @foreach($props['columnas'] ?? [] as $colIndex => $columna)
            <div class="p-2 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
                <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Columna {{ $colIndex + 1 }}</span>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columnas.{{ $colIndex }}.nombre"
                    placeholder="Ej: CAMPO, RESULTADO"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 mt-1">
            </div>
            @endforeach
        </div>
    </div>

    <!-- Campos -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Campos (etiquetas)</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">Define los campos que el bioquímico deberá completar</p>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['campos'] ?? [] as $index => $campo)
            <div class="p-3 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-zinc-400">Campo {{ $index + 1 }}</span>
                    <div class="flex-1"></div>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_values(array_filter($props['campos'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs font-medium">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <!-- Nombre del campo -->
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.nombre"
                    placeholder="Nombre del campo (ej: MUESTRA, COLOR...)"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 mb-2">

                <div class="grid grid-cols-2 gap-2">
                    <!-- Tipo de input -->
                    <div>
                        <label class="text-xs text-gray-500 dark:text-zinc-400">Tipo</label>
                        <select
                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.tipo_input"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            <option value="texto">Texto libre</option>
                            <option value="select">Selección</option>
                        </select>
                    </div>

                    <!-- Unidad de medida -->
                    <div>
                        <label class="text-xs text-gray-500 dark:text-zinc-400">Unidad (opcional)</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.unidad"
                            placeholder="Ej: UFC/ml"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                </div>

                <!-- Opciones (solo si tipo_input es select) -->
                @if(($campo['tipo_input'] ?? 'texto') === 'select')
                <div class="mt-2">
                    <label class="text-xs text-gray-500 dark:text-zinc-400">Opciones (separadas por coma)</label>
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.opciones"
                        placeholder="Ej: Menor a 100000,Mayor a 100000"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                </div>
                @endif

                <div class="border-t border-dashed border-zinc-300 dark:border-zinc-600 pt-2 mt-2">
                    <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400 mb-1 flex items-center gap-1">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Reactivos Químicos (por campo)
                    </p>
                    <p class="text-[10px] text-emerald-600/80 mb-2 leading-tight">Solo químicos. Material de toma va en plantilla base.</p>
                    <div class="space-y-1">
                        @foreach($campo['reactivos'] ?? [] as $ri => $reactivo)
                        <div class="border border-emerald-200 dark:border-emerald-700 rounded p-1.5 bg-white dark:bg-zinc-800 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-emerald-600 dark:text-emerald-400">Reactivo {{ $ri + 1 }}</span>
                                <button
                                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.reactivos', {{ json_encode(array_values(array_filter($campo['reactivos'] ?? [], fn($r, $i) => $i !== $ri, ARRAY_FILTER_USE_BOTH))) }})"
                                    class="px-1 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-500 rounded text-xs">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <select
                                wire:model.live="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.reactivos.{{ $ri }}.categoria_id"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                <option value="">Seleccionar categor&#237;a</option>
                                @foreach($categoriasInsumos as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                            @if(!empty($reactivo['categoria_id']))
                            <select
                                wire:model.live="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.reactivos.{{ $ri }}.reactivo_id"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                <option value="">Seleccionar insumo</option>
                                @foreach($insumosDisponibles->where('categoria_id', $reactivo['categoria_id']) as $ins)
                                    <option value="{{ $ins->id }}">{{ $ins->nombre }} ({{ $ins->unidadMedida->abreviatura ?? '' }})</option>
                                @endforeach
                            </select>
                            <input type="number"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.reactivos.{{ $ri }}.cantidad"
                                step="0.01" min="0.01" placeholder="Cantidad"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <button
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.reactivos', {{ json_encode(array_merge($campo['reactivos'] ?? [], [['categoria_id' => '', 'reactivo_id' => '', 'cantidad' => 1]])) }})"
                        class="mt-1 w-full px-2 py-1 border border-dashed border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-400 rounded text-xs hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors flex items-center justify-center gap-1">
                        <i class="fas fa-plus"></i> Agregar Reactivo
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_merge($props['campos'] ?? [], [['nombre' => '', 'tipo_input' => 'texto', 'opciones' => '', 'unidad' => '', 'reactivos' => []]])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Campo
        </flux:button>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Defina varios <strong>títulos alternativos</strong> y el bioquímico elegirá uno al capturar resultados.</li>
            <li>Cada campo puede ser de <strong>texto libre</strong> o <strong>selección</strong> (con opciones predefinidas).</li>
            <li>Opcionalmente puede agregar una <strong>unidad de medida</strong> que se mostrará junto al resultado.</li>
        </ul>
    </div>

</div>
