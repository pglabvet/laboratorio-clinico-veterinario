<!-- Propiedades de Tabla de Resultados -->
<div class="space-y-4">
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Descripción -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Descripción (opcional)</label>
        <textarea 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.descripcion"
            rows="2"
            placeholder="Ej: Perfil hormonal completo..."
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></textarea>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">Se mostrará debajo del título</p>
    </div>

    <!-- Columnas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Columnas</label>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['columnas'] ?? [] as $index => $columna)
            <div class="p-2 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Columna {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.columnas', {{ json_encode(array_values(array_filter($props['columnas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columnas.{{ $index }}.nombre"
                    placeholder="Ej: ANÁLISIS, RESULTADO, RANGOS DE REFERENCIA"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">Este será el encabezado visible en la tabla</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Filas predefinidas (análisis) -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Análisis predefinidos con valores por defecto</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">Define los análisis y sus rangos de referencia por defecto</p>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['filas'] ?? [] as $index => $fila)
            <div class="p-3 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Fila {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_values(array_filter($props['filas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="space-y-2">
                    <!-- Nombre del análisis -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Nombre del Análisis</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.nombre"
                            placeholder="Ej: T1, T2, T3..."
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                    
                    <!-- Rangos de referencia y unidad en la misma fila -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Rango de Referencia</label>
                            <input 
                                type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_ref"
                                placeholder="Ej: 1.0 - 4.0"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Unidad</label>
                            <input 
                                type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.unidad"
                                placeholder="Ej: ug/dl, ng/ml"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_merge($props['filas'] ?? [], [['nombre' => '', 'rango_ref' => '', 'unidad' => '']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Análisis
        </flux:button>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>La tabla tiene 3 columnas fijas: <strong>ANÁLISIS</strong> (nombre del examen), <strong>RESULTADO</strong> (valor obtenido) y <strong>RANGOS DE REFERENCIA</strong> (valores normales).</li>
            <li>Las columnas ANÁLISIS y RANGOS DE REFERENCIA se llenan automáticamente con los datos que configures aquí. <strong>No son editables</strong> por el bioquímico.</li>
            <li>Solo la columna <strong>RESULTADO</strong> es editable al momento de capturar resultados.</li>
            <li>Los rangos de referencia y unidades que definas aquí aparecerán precargados en el reporte final.</li>
            <li>Ideal para perfiles como: Perfil Hepático, Perfil Renal, Perfil Tiroideo, etc.</li>
        </ul>
    </div>
</div>
