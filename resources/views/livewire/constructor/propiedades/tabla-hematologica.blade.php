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
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['parametros_principales'] ?? [] as $index => $param)
            <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-800/50">
                <div class="flex gap-2 items-center mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Parámetro {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.parametros_principales', {{ json_encode(array_values(array_filter($props['parametros_principales'], fn($p, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="ml-auto px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs font-medium">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <div class="space-y-2">
                    <!-- Nombre del parámetro -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Nombre del Parámetro</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.nombre"
                            placeholder="Ej: LEUCOCITOS"
                            class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>

                    <!-- Tipo de rango -->
                    @php $rangoTipo = $param['rango_tipo'] ?? 'min-max'; @endphp
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Tipo de Rango</label>
                        <select
                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.rango_tipo"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            <option value="min-max">Rango (min - max)</option>
                            <option value="menor">Menor que (&lt;)</option>
                            <option value="menor-igual">Menor o igual (&le;)</option>
                            <option value="mayor">Mayor que (&gt;)</option>
                            <option value="mayor-igual">Mayor o igual (&ge;)</option>
                        </select>
                    </div>

                    @if($rangoTipo === 'min-max')
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Mínimo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.rango_min"
                                placeholder="Ej: 0"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Máximo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.rango_max"
                                placeholder="Ej: 3"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                    </div>
                    @else
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Valor</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.rango_valor"
                            placeholder="Ej: 3"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                    @endif

                    <!-- Unidad -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Unidad</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.parametros_principales.{{ $index }}.unidad"
                            placeholder="Ej: mm³, g/dl"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.parametros_principales', {{ json_encode(array_merge($props['parametros_principales'] ?? [], [['nombre' => '', 'unidad' => '', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '']])) }})"
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
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['diferenciales'] ?? [] as $index => $dif)
            <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-800/50">
                <div class="flex gap-2 items-center mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Diferencial {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.diferenciales', {{ json_encode(array_values(array_filter($props['diferenciales'], fn($d, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="ml-auto px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs font-medium">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Nombre</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.nombre"
                            placeholder="Nombre (ej: Segmentados)"
                            class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>

                    {{-- Rango Relativo (%) --}}
                    <div class="p-2 bg-blue-50/50 dark:bg-blue-900/10 rounded border border-blue-200/50 dark:border-blue-800/30">
                        <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 mb-2">Rango Relativo (%)</p>
                        @php $rangoRelTipo = $dif['rango_rel_tipo'] ?? 'min-max'; @endphp
                        <div class="space-y-2">
                            <select
                                wire:model.live="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.rango_rel_tipo"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                <option value="min-max">Rango (min - max)</option>
                                <option value="menor">Menor que (&lt;)</option>
                                <option value="menor-igual">Menor o igual (&le;)</option>
                                <option value="mayor">Mayor que (&gt;)</option>
                                <option value="mayor-igual">Mayor o igual (&ge;)</option>
                            </select>
                            @if($rangoRelTipo === 'min-max')
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text"
                                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.rango_rel_min"
                                    placeholder="Min"
                                    class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                <input type="text"
                                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.rango_rel_max"
                                    placeholder="Max"
                                    class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            </div>
                            @else
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.rango_rel_valor"
                                placeholder="Valor"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            @endif
                        </div>
                    </div>

                    {{-- Rango Absoluto (mm³) --}}
                    <div class="p-2 bg-green-50/50 dark:bg-green-900/10 rounded border border-green-200/50 dark:border-green-800/30">
                        <p class="text-xs font-semibold text-green-700 dark:text-green-400 mb-2">Rango Absoluto (mm³)</p>
                        @php $rangoAbsTipo = $dif['rango_abs_tipo'] ?? 'min-max'; @endphp
                        <div class="space-y-2">
                            <select
                                wire:model.live="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.rango_abs_tipo"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                <option value="min-max">Rango (min - max)</option>
                                <option value="menor">Menor que (&lt;)</option>
                                <option value="menor-igual">Menor o igual (&le;)</option>
                                <option value="mayor">Mayor que (&gt;)</option>
                                <option value="mayor-igual">Mayor o igual (&ge;)</option>
                            </select>
                            @if($rangoAbsTipo === 'min-max')
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text"
                                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.rango_abs_min"
                                    placeholder="Min"
                                    class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                <input type="text"
                                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.rango_abs_max"
                                    placeholder="Max"
                                    class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            </div>
                            @else
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.diferenciales.{{ $index }}.rango_abs_valor"
                                placeholder="Valor"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.diferenciales', {{ json_encode(array_merge($props['diferenciales'] ?? [], [['nombre' => '', 'rango_rel_tipo' => 'min-max', 'rango_rel_min' => '', 'rango_rel_max' => '', 'rango_rel_valor' => '', 'rango_abs_tipo' => 'min-max', 'rango_abs_min' => '', 'rango_abs_max' => '', 'rango_abs_valor' => '']])) }})"
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
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['indices'] ?? [] as $index => $indice)
            <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-800/50">
                <div class="flex gap-2 items-center mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Índice {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.indices', {{ json_encode(array_values(array_filter($props['indices'], fn($i, $idx) => $idx !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="ml-auto px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs font-medium">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Nombre</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.nombre"
                            placeholder="Nombre (ej: VCM)"
                            class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>

                    <!-- Tipo de rango -->
                    @php $rangoTipoIdx = $indice['rango_tipo'] ?? 'min-max'; @endphp
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Tipo de Rango</label>
                        <select
                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.rango_tipo"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            <option value="min-max">Rango (min - max)</option>
                            <option value="menor">Menor que (&lt;)</option>
                            <option value="menor-igual">Menor o igual (&le;)</option>
                            <option value="mayor">Mayor que (&gt;)</option>
                            <option value="mayor-igual">Mayor o igual (&ge;)</option>
                        </select>
                    </div>

                    @if($rangoTipoIdx === 'min-max')
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Mínimo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.rango_min"
                                placeholder="Ej: 60"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Máximo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.rango_max"
                                placeholder="Ej: 77"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                    </div>
                    @else
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Valor</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.rango_valor"
                            placeholder="Ej: 77"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                    @endif

                    <!-- Unidad -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Unidad</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.indices.{{ $index }}.unidad"
                            placeholder="Ej: fl, pg, g/dl"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.indices', {{ json_encode(array_merge($props['indices'] ?? [], [['nombre' => '', 'unidad' => '', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '']])) }})"
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
