<!-- Propiedades de Tabla de Dos Columnas -->
<div class="space-y-4">
    <!-- Título principal -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título Principal</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: EXAMEN MACROSCOPICO"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Secciones -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Secciones</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-3">Define las secciones de la tabla. Cada sección puede tener un subtítulo opcional y múltiples campos.</p>
        
        <div class="space-y-4 max-h-96 overflow-y-auto">
            @foreach($props['secciones'] ?? [] as $secIndex => $seccion)
            <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-700 dark:text-zinc-300">Sección {{ $secIndex + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones', {{ json_encode(array_values(array_filter($props['secciones'], fn($s, $i) => $i !== $secIndex, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i> Eliminar Sección
                    </button>
                </div>

                <!-- Subtítulo de la sección -->
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Subtítulo (opcional)</label>
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.subtitulo"
                        placeholder="Ej: EXAMEN MICROSCOPICO (dejar vacío si no necesitas subtítulo)"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                </div>

                <!-- Campos de la sección -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Campos</label>
                    <div class="space-y-2">
                        @foreach($seccion['campos'] ?? [] as $fieldIndex => $campo)
                        <div class="flex gap-2">
                            <input 
                                type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}"
                                placeholder="Ej: COLOR, CONSISTENCIA..."
                                class="flex-1 px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            <button 
                                wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos', {{ json_encode(array_values(array_filter($seccion['campos'], fn($f, $i) => $i !== $fieldIndex, ARRAY_FILTER_USE_BOTH))) }})"
                                class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    
                    <flux:button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos', {{ json_encode(array_merge($seccion['campos'] ?? [], [''])) }})"
                        variant="primary" 
                        icon="plus" 
                        size="sm"
                        class="w-full mt-2">
                        Agregar Campo
                    </flux:button>
                </div>
            </div>
            @endforeach
        </div>

        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones', {{ json_encode(array_merge($props['secciones'] ?? [], [['subtitulo' => '', 'campos' => ['']]])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-3">
            Agregar Sección
        </flux:button>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs text-blue-800 dark:text-blue-300">
            <i class="fas fa-info-circle mr-1"></i>
            Este componente crea una tabla de dos columnas. Puedes dividirla en secciones con subtítulos opcionales.
        </p>
    </div>
</div>
