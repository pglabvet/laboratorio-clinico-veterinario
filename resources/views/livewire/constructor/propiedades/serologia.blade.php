<!-- Propiedades de Serología -->
<div class="space-y-4">
    <!-- Título principal -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título Principal</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: SEROLOGIA"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Descripción -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Descripción (opcional)</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.descripcion"
            placeholder="Ej: PRUEBA RÁPIDA CON TÉCNICA DE INMUNOCROMATOGRAFÍA"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
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
                    placeholder="Ej: PRUEBA, RESULTADO"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 mt-1">
            </div>
            @endforeach
        </div>
    </div>

    <!-- Campos (pruebas serológicas) -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Pruebas</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-3">Define las pruebas serológicas. El bioquímico solo podrá marcar <strong>Negativo (-)</strong> o <strong>Positivo (+)</strong> para cada una.</p>
        
        <div class="space-y-2 max-h-96 overflow-y-auto">
            @foreach($props['campos'] ?? [] as $fieldIndex => $campo)
            <div class="flex gap-2">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $fieldIndex }}"
                    placeholder="Ej: Erlichia Canis, Leishmania infantum..."
                    class="flex-1 px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                <button 
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_values(array_filter($props['campos'], fn($f, $i) => $i !== $fieldIndex, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_merge($props['campos'] ?? [], [''])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Prueba
        </flux:button>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Crea una tabla de <strong>2 columnas</strong>: la izquierda muestra el nombre de la prueba y la derecha el resultado.</li>
            <li>El bioquímico solo puede seleccionar <strong>Negativo (-)</strong> o <strong>Positivo (+)</strong> como resultado.</li>
            <li>En el PDF, los resultados <strong>Positivo (+)</strong> se destacan en rojo.</li>
        </ul>
    </div>
</div>
