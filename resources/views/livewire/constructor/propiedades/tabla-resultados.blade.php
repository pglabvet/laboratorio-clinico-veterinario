<!-- Propiedades de Tabla de Resultados -->
<div class="space-y-4">
    <!-- Título -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Título</label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    <!-- Descripción -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Descripción (opcional)</label>
        <textarea 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.descripcion"
            rows="2"
            placeholder="Ej: Perfil hormonal completo..."
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></textarea>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">Se mostrará debajo del título</p>
    </div>

    <!-- Columnas -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Columnas</label>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @foreach($props['columnas'] ?? [] as $index => $columna)
            <div class="p-2 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Columna {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.columnas', {{ json_encode(array_values(array_filter($props['columnas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.columnas.{{ $index }}.nombre"
                    placeholder="Ej: ANÁLISIS, RESULTADO, RANGOS DE REFERENCIA"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">Este será el encabezado visible en la tabla</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Filas predefinidas (análisis) -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">Análisis predefinidos con valores por defecto</label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-2">Define los análisis y sus rangos de referencia por defecto</p>
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($props['filas'] ?? [] as $index => $fila)
            <div class="p-3 bg-gray-50 dark:bg-zinc-900 rounded border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Fila {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_values(array_filter($props['filas'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="space-y-2">
                    <!-- Nombre del análisis -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Nombre del Análisis</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.nombre"
                            placeholder="Ej: T1, T2, T3..."
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                    
                    <!-- Tipo de rango de referencia -->
                    @php $rangoTipo = $fila['rango_tipo'] ?? (empty($fila['rango_ref'] ?? '') ? 'min-max' : 'multiple'); @endphp
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
                            <option value="multiple">Múltiples rangos</option>
                        </select>
                    </div>

                    @if($rangoTipo === 'min-max')
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Mínimo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_min"
                                placeholder="Ej: 2.0"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Máximo</label>
                            <input type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_max"
                                placeholder="Ej: 6.2"
                                class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        </div>
                    </div>
                    @elseif(in_array($rangoTipo, ['menor', 'menor-igual', 'mayor', 'mayor-igual']))
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Valor</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rango_valor"
                            placeholder="Ej: 21"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                    @elseif($rangoTipo === 'multiple')
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Rangos de Referencia</label>
                        <div class="space-y-2">
                            @foreach(($fila['rangos'] ?? []) as $rIndex => $rangoEntry)
                            @php
                                $esNormal = $rangoEntry['es_normal'] ?? false;
                                $rTipo = $rangoEntry['tipo'] ?? 'min-max';
                                $rangosAfterRemove = array_values(array_filter($fila['rangos'] ?? [], fn($r, $i) => $i !== $rIndex, ARRAY_FILTER_USE_BOTH));
                            @endphp
                            <div class="p-2 rounded border {{ $esNormal ? 'bg-green-50 dark:bg-green-900/20 border-green-400 dark:border-green-600' : 'bg-gray-50 dark:bg-zinc-800 border-gray-200 dark:border-zinc-600' }}">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="flex items-center gap-1 text-xs">
                                        <input type="checkbox"
                                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rangos.{{ $rIndex }}.es_normal"
                                            class="rounded text-green-500">
                                        <span class="{{ $esNormal ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-500 dark:text-zinc-400' }}">Normal</span>
                                    </label>
                                    <button
                                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rangos', {{ json_encode($rangosAfterRemove) }})"
                                        class="px-1 py-0.5 text-red-500 hover:text-red-700 text-xs" title="Eliminar rango">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="grid grid-cols-3 gap-1">
                                    <select
                                        wire:model.live="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rangos.{{ $rIndex }}.tipo"
                                        class="px-1 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">
                                        <option value="min-max">min - max</option>
                                        <option value="menor">&lt;</option>
                                        <option value="menor-igual">&le;</option>
                                        <option value="mayor">&gt;</option>
                                        <option value="mayor-igual">&ge;</option>
                                    </select>
                                    @if($rTipo === 'min-max')
                                        <input type="text" wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rangos.{{ $rIndex }}.min"
                                            placeholder="Min" class="px-1 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">
                                        <input type="text" wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rangos.{{ $rIndex }}.max"
                                            placeholder="Max" class="px-1 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">
                                    @else
                                        <input type="text" wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rangos.{{ $rIndex }}.valor"
                                            placeholder="Valor" class="col-span-2 px-1 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">
                                    @endif
                                </div>
                                <input type="text"
                                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rangos.{{ $rIndex }}.etiqueta"
                                    placeholder="Etiqueta (ej: sin azotemia)"
                                    class="w-full mt-1 px-1 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-100">
                            </div>
                            @endforeach
                        </div>
                        @php
                            $newRangos = array_merge($fila['rangos'] ?? [], [['tipo' => 'min-max', 'min' => '', 'max' => '', 'valor' => '', 'etiqueta' => '', 'es_normal' => false]]);
                        @endphp
                        <button
                            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.rangos', {{ json_encode($newRangos) }})"
                            class="w-full mt-1 px-2 py-1 bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 text-gray-700 dark:text-zinc-300 rounded text-xs">
                            + Agregar rango
                        </button>
                    </div>
                    @endif

                    <!-- Unidad -->
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Unidad</label>
                        <input type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.unidad"
                            placeholder="Ej: ug/dl, ng/ml"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        @if($rangoTipo === 'multiple')
                            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">Dejar vacío si la unidad ya está en los rangos</p>
                        @endif
                    </div>

                    <!-- Reactivos de esta fila -->
                    <div class="border-t border-dashed border-zinc-300 dark:border-zinc-600 pt-2 mt-2">
                        <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400 mb-1 flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Reactivos Químicos (por fila)
                        </p>
                        <p class="text-[10px] text-emerald-600/80 mb-2 leading-tight">Solo químicos. Material de toma va en plantilla base.</p>
                        <div class="space-y-1">
                            @foreach($fila['reactivos'] ?? [] as $ri => $reactivo)
                            <div class="border border-emerald-200 dark:border-emerald-700 rounded p-1.5 bg-white dark:bg-zinc-800 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400">Reactivo {{ $ri + 1 }}</span>
                                    <button
                                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.reactivos', {{ json_encode(array_values(array_filter($fila['reactivos'] ?? [], fn($r, $i) => $i !== $ri, ARRAY_FILTER_USE_BOTH))) }})"
                                        class="px-1 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-500 rounded text-xs">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <select
                                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.reactivos.{{ $ri }}.categoria_id"
                                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                    <option value="">Seleccionar categoría</option>
                                    @foreach($categoriasInsumos as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                                @if(!empty($reactivo['categoria_id']))
                                <select
                                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.reactivos.{{ $ri }}.reactivo_id"
                                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                    <option value="">Seleccionar insumo</option>
                                    @foreach($insumosDisponibles->where('categoria_id', $reactivo['categoria_id']) as $ins)
                                        <option value="{{ $ins->id }}">{{ $ins->nombre }} ({{ $ins->unidadMedida->abreviatura ?? '' }})</option>
                                    @endforeach
                                </select>
                                <input type="number"
                                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.reactivos.{{ $ri }}.cantidad"
                                    step="0.01" min="0.01" placeholder="Cantidad"
                                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <button
                            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas.{{ $index }}.reactivos', {{ json_encode(array_merge($fila['reactivos'] ?? [], [['categoria_id' => '', 'reactivo_id' => '', 'cantidad' => 1]])) }})"
                            class="mt-1 w-full px-2 py-1 border border-dashed border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-400 rounded text-xs hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors flex items-center justify-center gap-1">
                            <i class="fas fa-plus"></i> Agregar Reactivo
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.filas', {{ json_encode(array_merge($props['filas'] ?? [], [['nombre' => '', 'rango_tipo' => 'min-max', 'rango_min' => '', 'rango_max' => '', 'rango_valor' => '', 'rango_ref' => '', 'rangos' => [], 'unidad' => '', 'reactivos' => []]])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-2">
            Agregar Análisis
        </flux:button>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>La tabla tiene 3 columnas fijas: <strong>ANÁLISIS</strong> (nombre del examen), <strong>RESULTADO</strong> (valor obtenido) y <strong>RANGOS DE REFERENCIA</strong> (valores normales).</li>
            <li>Las columnas ANÁLISIS y RANGOS DE REFERENCIA se llenan automáticamente con los datos que configures aquí. <strong>No son editables</strong> por el bioquímico.</li>
            <li>Solo la columna <strong>RESULTADO</strong> es editable al momento de capturar resultados.</li>
            <li>Los rangos de referencia y unidades que definas aquí aparecerán precargados en el reporte final.</li>
            <li>Ideal para perfiles como: Perfil Hepático, Perfil Renal, Perfil Tiroideo, etc.</li>
        </ul>
    </div>
</div>
