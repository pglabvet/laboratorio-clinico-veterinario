<!-- Propiedades de Tabla Temporal con Gráfica -->
<div class="space-y-4">
    <div class="text-xs text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    
    <!-- Título principal -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título Principal</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: Cortisol Basal"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Configuración de la gráfica -->
    <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-900">
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
            <i class="fas fa-chart-line mr-1"></i> Configuración de Gráfica
        </label>
        
        <div class="space-y-3">
            <!-- Mostrar gráfica -->
            <div class="flex items-center gap-2">
                <input 
                    type="checkbox" 
                    id="mostrar-grafica-{{ $componenteId }}"
                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.mostrar_grafica"
                    class="rounded border-gray-300 dark:border-zinc-700 text-blue-600">
                <label for="mostrar-grafica-{{ $componenteId }}" class="text-sm text-gray-700 dark:text-zinc-300">
                    Mostrar gráfica de líneas
                </label>
            </div>

            <!-- Etiqueta del eje Y -->
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Etiqueta Eje Y (Unidad)</label>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.unidad_medida"
                    placeholder="Ej: ug/dL"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            </div>
        </div>
    </div>

    <!-- Filas de análisis -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Análisis Temporales</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-3">Define los análisis que se medirán en diferentes momentos del tiempo.</p>
        
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['filas'] ?? [] as $filaIndex => $fila)
            <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-3 bg-white dark:bg-zinc-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-700 dark:text-zinc-300">Análisis {{ $filaIndex + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_values(array_filter($props['filas'], fn($f, $i) => $i !== $filaIndex, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>

                <div class="space-y-2">
                    <!-- Nombre del análisis -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">
                            <i class="fas fa-flask mr-1 text-yellow-500"></i> Nombre del Análisis
                        </label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $filaIndex }}.analisis"
                            placeholder="Ej: Cortisol basal 1ra"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>


                    <!-- Rangos de referencia -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">
                            <i class="fas fa-ruler mr-1 text-yellow-500"></i> Rangos de Referencia
                        </label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $filaIndex }}.rango_referencia"
                            placeholder="Ej: 2.0 - 6.0 ug/dL"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_merge($props['filas'] ?? [], [['analisis' => '', 'rango_referencia' => '']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-3">
            Agregar Análisis
        </flux:button>
    </div>

    <!-- Información del componente -->
    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs text-blue-800 dark:text-blue-300">
            <i class="fas fa-info-circle mr-1"></i>
            Este componente crea una tabla con análisis medidos en diferentes momentos del tiempo. 
            Los campos <span class="font-semibold text-yellow-700 dark:text-yellow-500">amarillos</span> (análisis y rangos) se configuran aquí, 
            mientras que los campos <span class="font-semibold text-green-700 dark:text-green-500">verdes</span> (hora y resultado) se llenarán al capturar resultados.
        </p>
    </div>
</div>
