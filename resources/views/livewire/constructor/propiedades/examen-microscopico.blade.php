<!-- Propiedades de Examen Microscópico -->
<div class="space-y-4">
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: EXAMEN MICROSCOPICO"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Títulos de columnas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Títulos de Columnas</label>
        <div class="grid grid-cols-3 gap-2">
            <input 
                type="text" 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columna_parametro"
                placeholder="PARÁMETRO"
                class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            <input 
                type="text" 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columna_resultado"
                placeholder="RESULTADO"
                class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            <input 
                type="text" 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columna_rango"
                placeholder="RANGO REF."
                class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
        </div>
    </div>

    <!-- Filas (parámetros) -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Parámetros</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">Define los parámetros y opcionalmente sus rangos de referencia</p>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['filas'] ?? [] as $index => $fila)
            <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-800/50">
                <div class="flex gap-2 items-center mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Parámetro {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_values(array_filter($props['filas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="ml-auto px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs font-medium">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <div class="space-y-2">
                    <!-- Nombre del parámetro -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Nombre del Parámetro</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.parametro"
                            placeholder="Ej: LEUCOCITOS"
                            class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>

                    <!-- Tipo de rango -->
                    @php $rangoTipo = $fila['rango_tipo'] ?? 'min-max'; @endphp
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Tipo de Rango</label>
                        <select
                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_tipo"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            <option value="min-max">Rango (min - max)</option>
                            <option value="menor">Menor que (&lt;)</option>
                            <option value="menor-igual">Menor o igual (&le;)</option>
                            <option value="mayor">Mayor que (&gt;)</option>
                            <option value="mayor-igual">Mayor o igual (&ge;)</option>
                        </select>
                    </div>

                    @if($rangoTipo === 'min-max')
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Mínimo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_min"
                                placeholder="Ej: 0"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Máximo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_max"
                                placeholder="Ej: 3"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                    </div>
                    @else
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Valor</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_valor"
                            placeholder="Ej: 3"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                    @endif

                    <!-- Unidad -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Unidad</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.unidad"
                            placeholder="Ej: c.m., ug/dl"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_merge($props['filas'] ?? [], [['parametro' => '', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'unidad' => '']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Parámetro
        </flux:button>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Muestra una tabla con 3 columnas: <strong>Parámetro</strong>, <strong>Resultado</strong> y <strong>Rango de Referencia</strong>.</li>
            <li>El administrador define los parámetros y sus rangos de referencia con campos estructurados.</li>
            <li>El bioquímico completa la columna del medio (resultado) con texto libre o números.</li>
            <li>El resultado se resalta en <strong class="text-red-600">rojo</strong> si sale del rango definido.</li>
            <li>Si ningún parámetro tiene rango de referencia, la columna derecha se oculta automáticamente.</li>
        </ul>
    </div>
</div>
