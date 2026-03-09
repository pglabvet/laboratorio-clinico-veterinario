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
        <div class="space-y-2 max-h-80 overflow-y-auto">
            @foreach($props['filas'] ?? [] as $index => $fila)
            <div class="flex gap-2 items-start">
                <div class="flex-1 space-y-1">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.parametro"
                        placeholder="Nombre del parámetro (ej: LEUCOCITOS)"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_referencia"
                        placeholder="Rango de referencia (opcional, ej: < 3 c.m.)"
                        class="w-full px-2 py-2 border border-gray-200 dark:border-zinc-600 rounded text-sm bg-gray-50 dark:bg-zinc-900 text-gray-700 dark:text-zinc-300">
                </div>
                <button 
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_values(array_filter($props['filas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs font-medium mt-1">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_merge($props['filas'] ?? [], [['parametro' => '', 'rango_referencia' => '']])) }})"
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
            <li>El administrador define los parámetros (columna izquierda) y opcionalmente los rangos de referencia (columna derecha).</li>
            <li>El bioquímico completa la columna del medio (resultado) con texto libre o números.</li>
            <li>Si ningún parámetro tiene rango de referencia, la columna derecha se oculta automáticamente.</li>
        </ul>
    </div>
</div>
