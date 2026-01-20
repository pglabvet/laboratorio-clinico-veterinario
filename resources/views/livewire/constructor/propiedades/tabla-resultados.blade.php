<!-- Propiedades de Tabla de Resultados -->
<div class="space-y-4">
    <!-- Debug -->
    <div class="text-xs text-red-500 bg-red-50 p-2 rounded">
        Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? 'N/A' }}
    </div>
    
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <!-- Descripción -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción (opcional)</label>
        <textarea 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.descripcion"
            rows="2"
            placeholder="Ej: Perfil hormonal completo..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
        <p class="text-xs text-gray-500 mt-1">Se mostrará debajo del título</p>
    </div>

    <!-- Columnas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Columnas</label>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['columnas'] ?? [] as $index => $columna)
            <div class="p-2 bg-gray-50 rounded border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600">Columna {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.columnas', {{ json_encode(array_values(array_filter($props['columnas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columnas.{{ $index }}.nombre"
                    placeholder="Ej: ANÁLISIS, RESULTADO, RANGOS DE REFERENCIA"
                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                <p class="text-xs text-gray-500 mt-1">Este será el encabezado visible en la tabla</p>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.columnas', {{ json_encode(array_merge($props['columnas'] ?? [], [['nombre' => '']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Columna
        </flux:button>
    </div>

    <!-- Filas predefinidas (análisis) -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Análisis predefinidos</label>
        <p class="text-xs text-gray-500 mb-2">Define los nombres de los análisis que aparecerán en la tabla</p>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['filas'] ?? [] as $index => $fila)
            <div class="flex gap-2">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}"
                    placeholder="Ej: T4, T3, TSH, Glicemia..."
                    class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm">
                <button 
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_values(array_filter($props['filas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-2 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_merge($props['filas'] ?? [], [''])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Análisis
        </flux:button>
    </div>

    <div class="p-3 bg-blue-50 rounded border border-blue-200">
        <p class="text-xs text-blue-800">
            <i class="fas fa-info-circle mr-1"></i>
            El bioquímico solo podrá editar las columnas de RESULTADO y RANGOS DE REFERENCIA. Los nombres de análisis son fijos.
        </p>
    </div>
</div>
