<!-- Propiedades de Tabla Hematológica -->
<div class="space-y-4">
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: CUADRO HEMÁTICO"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Parámetros Principales -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Parámetros Principales (Lado Izquierdo)</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">Eritrocitos, Leucócitos, Hematocrito, etc.</p>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['parametros_principales'] ?? [] as $index => $param)
            <div class="border border-gray-300 dark:border-zinc-700 rounded p-2 bg-gray-50 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300">Parámetro {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.parametros_principales', {{ json_encode(array_values(array_filter($props['parametros_principales'], fn($p, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.nombre"
                        placeholder="Nombre"
                        class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.unidad"
                        placeholder="Unidad"
                        class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.ref_min"
                        placeholder="Ref Min"
                        class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.ref_max"
                        placeholder="Ref Max"
                        class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
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
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Diferenciales (Lado Derecho)</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">Cayados, Segmentados, Eosinófilos, etc. Con valores relativos y absolutos</p>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['diferenciales'] ?? [] as $index => $dif)
            <div class="border border-gray-300 dark:border-zinc-700 rounded p-2 bg-gray-50 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300">Diferencial {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.diferenciales', {{ json_encode(array_values(array_filter($props['diferenciales'], fn($d, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="space-y-1">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.nombre"
                        placeholder="Nombre (ej: Segmentados)"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <div class="grid grid-cols-4 gap-1">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.ref_rel_min"
                            placeholder="Rel Min"
                            class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.ref_rel_max"
                            placeholder="Rel Max"
                            class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.ref_abs_min"
                            placeholder="Abs Min"
                            class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.ref_abs_max"
                            placeholder="Abs Max"
                            class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
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
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Índices Eritrocitarios</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">VCM, HbCM, CCMHb</p>
        <div class="space-y-2">
            @foreach($props['indices'] ?? [] as $index => $indice)
            <div class="border border-gray-300 dark:border-zinc-700 rounded p-2 bg-gray-50 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-700 dark:text-zinc-300">Índice {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.indices', {{ json_encode(array_values(array_filter($props['indices'], fn($i, $idx) => $idx !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="space-y-1">
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.nombre"
                        placeholder="Nombre (ej: VCM)"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <div class="grid grid-cols-2 gap-1">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.unidad"
                            placeholder="Unidad (ej: fl)"
                            class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.referencia"
                            placeholder="Referencia (ej: vn 60-77 fl)"
                            class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
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

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li><strong>Parámetros Principales (izquierda):</strong> Son los valores básicos del hemograma (Eritrocitos, Leucocitos, Hematocrito, Hemoglobina, etc.). Cada uno tiene unidad y rango de referencia min/max.</li>
            <li><strong>Diferenciales (derecha):</strong> Conteo diferencial leucocitario (Segmentados, Eosinófilos, Basófilos, Linfocitos, Monocitos). Tienen valores relativos (%) y absolutos.</li>
            <li><strong>Índices Eritrocitarios (abajo):</strong> VCM, HbCM, CCMHb con su unidad y valor de referencia.</li>
            <li>El bioquímico solo completará los campos de <strong>resultado</strong>. Los nombres, unidades y rangos se precargan con lo que configures aquí.</li>
        </ul>
    </div>
</div>
