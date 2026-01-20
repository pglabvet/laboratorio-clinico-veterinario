<!-- Propiedades de Tabla de Dos Columnas -->
<div class="space-y-4">
    <div class="text-xs text-red-500 bg-red-50 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    <!-- Título principal -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Título Principal</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: EXAMEN MACROSCOPICO"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <!-- Secciones -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Secciones</label>
        <p class="text-xs text-gray-500 mb-3">Define las secciones de la tabla. Cada sección puede tener un subtítulo opcional y múltiples campos.</p>
        
        <div class="space-y-4 max-h-96 overflow-y-auto">
            @foreach($props['secciones'] ?? [] as $secIndex => $seccion)
            <div class="border border-gray-300 rounded-lg p-3 bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-700">Sección {{ $secIndex + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones', {{ json_encode(array_values(array_filter($props['secciones'], fn($s, $i) => $i !== $secIndex, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs">
                        <i class="fas fa-trash"></i> Eliminar Sección
                    </button>
                </div>

                <!-- Subtítulo de la sección -->
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subtítulo (opcional)</label>
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.subtitulo"
                        placeholder="Ej: EXAMEN MICROSCOPICO (dejar vacío si no necesitas subtítulo)"
                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                </div>

                <!-- Campos de la sección -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campos</label>
                    <div class="space-y-2">
                        @foreach($seccion['campos'] ?? [] as $fieldIndex => $campo)
                        <div class="flex gap-2">
                            <input 
                                type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}"
                                placeholder="Ej: COLOR, CONSISTENCIA..."
                                class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm">
                            <button 
                                wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos', {{ json_encode(array_values(array_filter($seccion['campos'], fn($f, $i) => $i !== $fieldIndex, ARRAY_FILTER_USE_BOTH))) }})"
                                class="px-2 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs">
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

    <div class="p-3 bg-blue-50 rounded border border-blue-200">
        <p class="text-xs text-blue-800">
            <i class="fas fa-info-circle mr-1"></i>
            Este componente crea una tabla de dos columnas. Puedes dividirla en secciones con subtítulos opcionales.
        </p>
    </div>
</div>
