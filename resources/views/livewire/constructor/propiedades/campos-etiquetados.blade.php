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
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_merge($props['campos'] ?? [], [['nombre' => '', 'tipo_input' => 'texto', 'opciones' => '', 'unidad' => '']])) }})"
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
