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
                    <div class="space-y-3">
                        @foreach($seccion['campos'] ?? [] as $fieldIndex => $campo)
                        <div class="border border-gray-200 dark:border-zinc-700 rounded p-2 bg-white dark:bg-zinc-800">
                            <div class="flex gap-2 items-center mb-1">
                                <input 
                                    type="text"
                                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}.nombre"
                                    placeholder="Ej: COLOR, CONSISTENCIA..."
                                    class="flex-1 px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                <select
                                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}.tipo_input"
                                    class="px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                    <option value="input">Texto libre</option>
                                    <option value="select">Seleccionable</option>
                                </select>
                                <button 
                                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos', {{ json_encode(array_values(array_filter($seccion['campos'], fn($f, $i) => $i !== $fieldIndex, ARRAY_FILTER_USE_BOTH))) }})"
                                    class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            @if(($campo['tipo_input'] ?? 'input') === 'select')
                            <input 
                                type="text"
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}.opciones"
                                placeholder="Opciones separadas por coma (ej: AMARILLO,VERDE,ROJO,AMBAR)"
                                class="w-full px-3 py-2 border border-gray-200 dark:border-zinc-600 rounded text-sm bg-gray-50 dark:bg-zinc-900 text-gray-700 dark:text-zinc-300 mt-1">
                            @endif
                            <!-- Reactivos de este campo -->
                            <div class="border-t border-dashed border-zinc-300 dark:border-zinc-600 pt-2 mt-2">
                                <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400 mb-1 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    Reactivos Químicos (por campo)
                                </p>
                                <p class="text-[10px] text-emerald-600/80 mb-2 leading-tight">Solo químicos. Material de toma va en plantilla base.</p>
                                <div class="space-y-1">
                                    @foreach($campo['reactivos'] ?? [] as $ri => $reactivo)
                                    <div class="border border-emerald-200 dark:border-emerald-700 rounded p-1.5 bg-white dark:bg-zinc-900 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-emerald-600 dark:text-emerald-400">Reactivo {{ $ri + 1 }}</span>
                                            <button
                                                wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}.reactivos', {{ json_encode(array_values(array_filter($campo['reactivos'] ?? [], fn($r, $i) => $i !== $ri, ARRAY_FILTER_USE_BOTH))) }})"
                                                class="px-1 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-500 rounded text-xs">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <select
                                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}.reactivos.{{ $ri }}.categoria_id"
                                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                            <option value="">Seleccionar categoria</option>
                                            @foreach($categoriasInsumos as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                            @endforeach
                                        </select>
                                        @if(!empty($reactivo['categoria_id']))
                                        <select
                                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}.reactivos.{{ $ri }}.reactivo_id"
                                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                            <option value="">Seleccionar insumo</option>
                                            @foreach($insumosDisponibles->where('categoria_id', $reactivo['categoria_id']) as $ins)
                                                <option value="{{ $ins->id }}">{{ $ins->nombre }} ({{ $ins->unidadMedida->abreviatura ?? '' }})</option>
                                            @endforeach
                                        </select>
                                        <input type="number"
                                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}.reactivos.{{ $ri }}.cantidad"
                                            step="0.01" min="0.01" placeholder="Cantidad"
                                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                <button
                                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos.{{ $fieldIndex }}.reactivos', {{ json_encode(array_merge($campo['reactivos'] ?? [], [['categoria_id' => '', 'reactivo_id' => '', 'cantidad' => 1]])) }})"
                                    class="mt-1 w-full px-2 py-1 border border-dashed border-emerald-400 dark:border-emerald-600 text-emerald-700 dark:text-emerald-400 rounded text-xs hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors flex items-center justify-center gap-1">
                                    <i class="fas fa-plus"></i> Agregar Reactivo Químico
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <flux:button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $secIndex }}.campos', {{ json_encode(array_merge($seccion['campos'] ?? [], [['nombre' => '', 'tipo_input' => 'input', 'opciones' => '', 'reactivos' => []]])) }})"
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
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones', {{ json_encode(array_merge($props['secciones'] ?? [], [['subtitulo' => '', 'campos' => [['nombre' => '', 'tipo_input' => 'input', 'opciones' => '', 'reactivos' => []]]]])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-3">
            Agregar Sección
        </flux:button>
    </div>

    {{-- Reactivos Globales del componente --}}
    <div class="border border-emerald-200 dark:border-emerald-800 rounded-lg p-3 bg-emerald-50/50 dark:bg-emerald-900/10">
        <p class="text-xs font-medium text-emerald-700 dark:text-emerald-400 mb-1 flex items-center gap-1">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
            Reactivos Químicos (Globales de la Tabla)
        </p>
        <p class="text-[10px] text-emerald-600/80 dark:text-emerald-500/80 mb-2 leading-tight">
            Se descuentan 1 sola vez por enviar esta tabla completa (Kit general). Adicional a los reactivos por campo.
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
                <!-- Select de Categoría -->
                <select
                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.reactivos.{{ $ri }}.categoria_id"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <option value="">Seleccionar categoría</option>
                    @foreach($categoriasInsumos as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>
                <!-- Select de Insumo -->
                @if(!empty($reactivo['categoria_id']))
                <select
                    wire:model.live="componentes.{{ $indiceComponente }}.propiedades.reactivos.{{ $ri }}.reactivo_id"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    <option value="">Seleccionar insumo</option>
                    @foreach($insumosDisponibles->where('categoria_id', $reactivo['categoria_id']) as $ins)
                        <option value="{{ $ins->id }}">{{ $ins->nombre }} ({{ $ins->unidadMedida->abreviatura ?? '' }})</option>
                    @endforeach
                </select>
                <!-- Input Cantidad -->
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
            <i class="fas fa-plus"></i> Agregar Reactivo Global
        </button>
    </div>

    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Crea una tabla de <strong>2 columnas</strong>: la izquierda muestra el nombre del campo (fijo) y la derecha es donde el bioquímico escribe el resultado.</li>
            <li>Puedes organizarla en <strong>secciones</strong> con subtítulos opcionales.</li>
            <li>Cada campo puede ser de <strong>texto libre</strong> (el bioquímico escribe lo que quiera) o <strong>seleccionable</strong> (elige entre opciones predefinidas separadas por coma).</li>
        </ul>
    </div>
</div>
