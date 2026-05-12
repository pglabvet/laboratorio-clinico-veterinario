<!-- Propiedades de Examen Microscópico -->
<div class="space-y-4">
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: EXAMEN MICROSCOPICO"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Títulos de columnas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Títulos de Columnas</label>
        <div class="grid grid-cols-3 gap-2">
            <input 
                type="text" 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columna_parametro"
                placeholder="PARÁMETRO"
                class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            <input 
                type="text" 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columna_resultado"
                placeholder="RESULTADO"
                class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            <input 
                type="text" 
                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columna_rango"
                placeholder="RANGO REF."
                class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
        </div>
    </div>

    <!-- Filas (parámetros) -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Parámetros</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">Define los parámetros y opcionalmente sus rangos de referencia</p>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['filas'] ?? [] as $index => $fila)
            <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-800/50">
                <div class="flex gap-2 items-center mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Parámetro {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_values(array_filter($props['filas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
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
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.parametro"
                            placeholder="Ej: LEUCOCITOS"
                            class="w-full px-2 py-2 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>

                    <!-- Tipo de rango -->
                    @php $rangoTipo = $fila['rango_tipo'] ?? 'min-max'; @endphp
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Tipo de Rango</label>
                        <select
                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_tipo"
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
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_min"
                                placeholder="Ej: 0"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Máximo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_max"
                                placeholder="Ej: 3"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                    </div>
                    @else
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Valor</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_valor"
                            placeholder="Ej: 3"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                    @endif

                    <!-- Unidad -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Unidad</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.unidad"
                            placeholder="Ej: c.m., ug/dl"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_merge($props['filas'] ?? [], [['parametro' => '', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'unidad' => '']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Parámetro
        </flux:button>
    </div>

    {{-- Reactivos del componente --}}
    <div class="border border-emerald-200 dark:border-emerald-800 rounded-lg p-3 bg-emerald-50/50 dark:bg-emerald-900/10">
        <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400 mb-1 flex items-center gap-1">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
            Reactivos Químicos / Clínicos
        </p>
        <p class="text-[10px] text-emerald-600/80 dark:text-emerald-500/80 mb-2 leading-tight">
            Se descuentan al <b>guardar resultados</b>. (Jeringas/tubos van en Material de Toma).
        </p>
        <div class="space-y-2">
            @foreach($props['reactivos'] ?? [] as $ri => $reactivo)
            <div class="border border-emerald-200 dark:border-emerald-700 rounded p-2 bg-white dark:bg-zinc-800 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Reactivo {{ $ri + 1 }}</span>
                    <button
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.reactivos', {{ json_encode(array_values(array_filter($props['reactivos'], fn($r, $i) => $i !== $ri, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <select
                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.reactivos.{{ $ri }}.categoria_id"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <option value="">Seleccionar categoría</option>
                    @foreach($categoriasInsumos as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
                @if(!empty($reactivo['categoria_id']))
                <select
                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.reactivos.{{ $ri }}.reactivo_id"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <option value="">Seleccionar insumo</option>
                    @foreach($insumosDisponibles->where('categoria_id', $reactivo['categoria_id']) as $ins)
                        <option value="{{ $ins->id }}">{{ $ins->nombre }} ({{ $ins->unidadMedida->abreviatura ?? '' }})</option>
                    @endforeach
                </select>
                <input type="number"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.reactivos.{{ $ri }}.cantidad"
                    step="0.01" min="0.01" placeholder="Cantidad"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                @endif
            </div>
            @endforeach
        </div>
        <button
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.reactivos', {{ json_encode(array_merge($props['reactivos'] ?? [], [['categoria_id' => '', 'reactivo_id' => '', 'cantidad' => 1]])) }})"
            class="mt-2 w-full px-2 py-1.5 border border-dashed border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-400 rounded text-xs hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors flex items-center justify-center gap-1">
            <i class="fas fa-plus"></i> Agregar Reactivo Químico
        </button>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Muestra una tabla con 3 columnas: <strong>Parámetro</strong>, <strong>Resultado</strong> y <strong>Rango de Referencia</strong>.</li>
            <li>El administrador define los parámetros y sus rangos de referencia con campos estructurados.</li>
            <li>El bioquímico completa la columna del medio (resultado) con texto libre o números.</li>
            <li>El resultado se resalta en <strong class="text-red-600">rojo</strong> si sale del rango definido.</li>
            <li>Si ningún parámetro tiene rango de referencia, la columna derecha se oculta automáticamente.</li>
        </ul>
    </div>
</div>
