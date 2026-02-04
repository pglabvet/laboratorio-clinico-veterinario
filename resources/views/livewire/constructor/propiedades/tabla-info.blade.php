<!-- Propiedades de Tabla de Información -->
<div class="space-y-4">
    <div class="text-xs text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Número de columnas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Columnas por fila</label>
        <select 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columnas"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            <option value="1">1 columna</option>
            <option value="2">2 columnas</option>
            <option value="3">3 columnas</option>
        </select>
    </div>

    <!-- Filas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Campos</label>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['filas'] ?? [] as $index => $fila)
            <div class="p-2 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Campo {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_values(array_filter($props['filas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-xs">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.label"
                    placeholder="Etiqueta (ej: PACIENTE)"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs mb-1 bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.placeholder"
                    placeholder="Placeholder"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            </div>
            @endforeach
        </div>
        
        <button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_merge($props['filas'] ?? [], [['label' => '', 'campo' => '', 'tipo' => 'texto', 'placeholder' => '']])) }})"
            class="w-full mt-2 px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
            <i class="fas fa-plus mr-1"></i> Agregar Campo
        </button>
    </div>
</div>
