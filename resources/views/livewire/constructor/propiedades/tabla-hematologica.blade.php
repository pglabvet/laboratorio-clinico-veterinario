<!-- Propiedades de Tabla Hematológica -->
<div class="space-y-4">
    <div class="text-xs text-red-500 bg-red-50 p-2 rounded mb-2">Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}</div>
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: CUADRO HEMÁTICO"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <!-- Parámetros Principales -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Parámetros Principales (Lado Izquierdo)</label>
        <p class="text-xs text-gray-500 mb-2">Eritrocitos, Leucócitos, Hematocrito, etc.</p>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['parametros_principales'] ?? [] as $index => $param)
            <div class="border border-gray-300 rounded p-2 bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-700">Parámetro {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.parametros_principales', {{ json_encode(array_values(array_filter($props['parametros_principales'], fn($p, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.nombre"
                        placeholder="Nombre"
                        class="px-2 py-1 border border-gray-300 rounded text-xs">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.unidad"
                        placeholder="Unidad"
                        class="px-2 py-1 border border-gray-300 rounded text-xs">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.ref_min"
                        placeholder="Ref Min"
                        class="px-2 py-1 border border-gray-300 rounded text-xs">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.ref_max"
                        placeholder="Ref Max"
                        class="px-2 py-1 border border-gray-300 rounded text-xs">
                </div>
            </div>
            @endforeach
        </div>
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.parametros_principales', {{ json_encode(array_merge($props['parametros_principales'] ?? [], [['nombre' => '', 'unidad' => '', 'ref_min' => '', 'ref_max' => '']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Parámetro
        </flux:button>
    </div>

    <!-- Diferenciales -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Diferenciales (Lado Derecho)</label>
        <p class="text-xs text-gray-500 mb-2">Cayados, Segmentados, Eosinófilos, etc. Con valores relativos y absolutos</p>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['diferenciales'] ?? [] as $index => $dif)
            <div class="border border-gray-300 rounded p-2 bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-700">Diferencial {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.diferenciales', {{ json_encode(array_values(array_filter($props['diferenciales'], fn($d, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="space-y-1">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.nombre"
                        placeholder="Nombre (ej: Segmentados)"
                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                    <div class="grid grid-cols-4 gap-1">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.ref_rel_min"
                            placeholder="Rel Min"
                            class="px-2 py-1 border border-gray-300 rounded text-xs">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.ref_rel_max"
                            placeholder="Rel Max"
                            class="px-2 py-1 border border-gray-300 rounded text-xs">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.ref_abs_min"
                            placeholder="Abs Min"
                            class="px-2 py-1 border border-gray-300 rounded text-xs">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.ref_abs_max"
                            placeholder="Abs Max"
                            class="px-2 py-1 border border-gray-300 rounded text-xs">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.diferenciales', {{ json_encode(array_merge($props['diferenciales'] ?? [], [['nombre' => '', 'ref_rel_min' => '', 'ref_rel_max' => '', 'ref_abs_min' => '', 'ref_abs_max' => '']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Diferencial
        </flux:button>
    </div>

    <!-- Índices Eritrocitarios -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Índices Eritrocitarios</label>
        <p class="text-xs text-gray-500 mb-2">VCM, HbCM, CCMHb</p>
        <div class="space-y-2">
            @foreach($props['indices'] ?? [] as $index => $indice)
            <div class="border border-gray-300 rounded p-2 bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-700">Índice {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.indices', {{ json_encode(array_values(array_filter($props['indices'], fn($i, $idx) => $idx !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="space-y-1">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.nombre"
                        placeholder="Nombre (ej: VCM)"
                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                    <div class="grid grid-cols-2 gap-1">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.unidad"
                            placeholder="Unidad (ej: fl)"
                            class="px-2 py-1 border border-gray-300 rounded text-xs">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.referencia"
                            placeholder="Referencia (ej: vn 60-77 fl)"
                            class="px-2 py-1 border border-gray-300 rounded text-xs">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.indices', {{ json_encode(array_merge($props['indices'] ?? [], [['nombre' => '', 'unidad' => '', 'referencia' => '']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Índice
        </flux:button>
    </div>

    <div class="p-3 bg-purple-50 rounded border border-purple-200">
        <p class="text-xs text-purple-800">
            <i class="fas fa-info-circle mr-1"></i>
            Tabla especializada para análisis hematológicos con parámetros principales, diferenciales e índices eritrocitarios.
        </p>
    </div>
</div>
