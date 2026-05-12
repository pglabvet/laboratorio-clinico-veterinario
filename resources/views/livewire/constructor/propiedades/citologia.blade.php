<!-- Propiedades de Citología -->
<div class="space-y-4">
    {{-- Títulos alternativos --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
            <i class="fas fa-heading mr-1 text-blue-500"></i> Títulos Alternativos
        </label>
        <p class="text-[10px] text-gray-500 dark:text-zinc-400 mb-2 leading-tight">El bioquímico podrá elegir uno de estos títulos al capturar resultados</p>
        <div class="space-y-2 max-h-40 overflow-y-auto">
            @foreach($props['titulos'] ?? [] as $tIndex => $titulo)
            <div class="flex gap-2">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulos.{{ $tIndex }}"
                    placeholder="Ej: CITOLOGÍA, HISTOLOGÍA..."
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                @if(count($props['titulos'] ?? []) > 1)
                <button 
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.titulos', {{ json_encode(array_values(array_filter($props['titulos'], fn($f, $i) => $i !== $tIndex, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded-lg text-xs font-medium">
                    <i class="fas fa-trash"></i>
                </button>
                @endif
            </div>
            @endforeach
        </div>
        
        <button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.titulos', {{ json_encode(array_merge($props['titulos'] ?? [], [''])) }})"
            class="mt-2 w-full px-2 py-1.5 border border-dashed border-gray-400 dark:border-zinc-600 text-gray-700 dark:text-zinc-300 rounded text-xs hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-center gap-1">
            <i class="fas fa-plus"></i> Agregar Título
        </button>
    </div>

    {{-- Tumores (Opciones seleccionables) --}}
    <div class="border border-purple-200 dark:border-purple-800 rounded-lg p-3 bg-purple-50/50 dark:bg-purple-900/10">
        <label class="block text-sm font-medium text-purple-700 dark:text-purple-400 mb-2">
            <i class="fas fa-disease mr-1"></i> Tipos de Tumor (Opciones del Selector)
        </label>
        <p class="text-[10px] text-purple-600/80 dark:text-purple-500/80 mb-3 leading-tight">
            El bioquímico seleccionará uno de estos tumores al capturar resultados. Las secciones dependientes se actualizarán automáticamente.
        </p>

        <div class="space-y-2 max-h-48 overflow-y-auto">
            @foreach($props['tumores'] ?? [] as $ti => $tumor)
            <div class="flex items-center gap-2">
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.tumores.{{ $ti }}"
                    class="flex-1 px-2 py-1.5 border border-purple-200 dark:border-purple-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"
                    placeholder="Nombre del tumor">
                <button
                    wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.tumores', {{ json_encode(array_values(array_filter($props['tumores'], fn($t, $i) => $i !== $ti, ARRAY_FILTER_USE_BOTH))) }})"
                    class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs flex-shrink-0">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            @endforeach
        </div>

        <button
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.tumores', {{ json_encode(array_merge($props['tumores'] ?? [], [''])) }})"
            class="mt-2 w-full px-2 py-1.5 border border-dashed border-purple-400 dark:border-purple-600 text-purple-700 dark:text-purple-400 rounded text-xs hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors flex items-center justify-center gap-1">
            <i class="fas fa-plus"></i> Agregar Tipo de Tumor
        </button>
    </div>

    {{-- Secciones --}}
    <div class="border border-blue-200 dark:border-blue-800 rounded-lg p-3 bg-blue-50/50 dark:bg-blue-900/10">
        <label class="block text-sm font-medium text-blue-700 dark:text-blue-400 mb-2">
            <i class="fas fa-layer-group mr-1"></i> Secciones del Informe
        </label>
        <p class="text-[10px] text-blue-600/80 dark:text-blue-500/80 mb-3 leading-tight">
            Configure cada sección del informe citológico. Puede agregar, quitar o reordenar secciones.
        </p>

        <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1">
            @foreach($props['secciones'] ?? [] as $si => $seccion)
            <div class="border border-blue-200 dark:border-blue-700 rounded-lg p-3 bg-white dark:bg-zinc-800 space-y-3">
                {{-- Header de la sección --}}
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-blue-700 dark:text-blue-400">
                        <i class="fas fa-bookmark mr-1"></i> Sección {{ $si + 1 }}
                    </span>
                    <button
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones', {{ json_encode(array_values(array_filter($props['secciones'], fn($s, $i) => $i !== $si, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                {{-- Título de sección --}}
                <div>
                    <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Título de la Sección</label>
                    <input 
                        type="text"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $si }}.titulo"
                        placeholder="Ej: Observación macroscópica"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                </div>

                {{-- Tipo de sección --}}
                <div>
                    <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Comportamiento</label>
                    <select 
                        wire:model.live="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $si }}.tipo"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                        <option value="editable">✏️ Editable (texto libre para el bioquímico)</option>
                        <option value="dependiente">🔗 Dependiente del tumor (texto diferente por tumor)</option>
                        <option value="con_tumor">📌 Con tumor (texto fijo + tumor seleccionado)</option>
                    </select>
                    <p class="text-[10px] text-gray-500 dark:text-zinc-500 mt-1">
                        @if(($seccion['tipo'] ?? 'editable') === 'editable')
                            El bioquímico podrá editar libremente el texto base al capturar resultados.
                        @elseif(($seccion['tipo'] ?? '') === 'dependiente')
                            El texto se cargará automáticamente según el tumor seleccionado. Defina un texto para cada tumor abajo.
                        @else
                            El texto base se concatenará con el nombre del tumor seleccionado.
                        @endif
                    </p>
                </div>

                {{-- Texto base (para editable y con_tumor) --}}
                @if(($seccion['tipo'] ?? 'editable') !== 'dependiente')
                <div>
                    <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Texto Base</label>
                    <textarea 
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $si }}.texto_base"
                        rows="3"
                        placeholder="Escriba el texto base de esta sección..."
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></textarea>
                </div>
                @endif

                {{-- Textos por tumor (solo para secciones dependientes) --}}
                @if(($seccion['tipo'] ?? 'editable') === 'dependiente')
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-amber-700 dark:text-amber-400">
                        <i class="fas fa-exchange-alt mr-1"></i> Texto por cada Tipo de Tumor
                    </label>
                    @foreach($props['tumores'] ?? [] as $ti => $tumor)
                        @if(!empty($tumor))
                        <div>
                            <label class="block text-[10px] font-medium text-gray-600 dark:text-zinc-400 mb-0.5">
                                <span class="inline-block w-2 h-2 bg-purple-500 rounded-full mr-1"></span>
                                {{ $tumor }}
                            </label>
                            <textarea 
                                wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.secciones.{{ $si }}.textos_por_tumor.{{ $tumor }}"
                                rows="2"
                                placeholder="Texto para {{ $tumor }}..."
                                class="w-full px-2 py-1 border border-amber-200 dark:border-amber-700 rounded text-xs bg-amber-50/50 dark:bg-amber-900/10 text-gray-900 dark:text-zinc-100"></textarea>
                        </div>
                        @endif
                    @endforeach

                    @if(empty(array_filter($props['tumores'] ?? [])))
                    <p class="text-[10px] text-amber-600 dark:text-amber-400 italic">
                        ⚠️ Agregue tipos de tumor arriba para definir textos específicos.
                    </p>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Botón agregar sección --}}
        <button
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.secciones', {{ json_encode(array_merge($props['secciones'] ?? [], [['titulo' => '', 'texto_base' => '', 'tipo' => 'editable', 'usa_tumor' => false, 'textos_por_tumor' => []]])) }})"
            class="mt-3 w-full px-2 py-1.5 border border-dashed border-blue-400 dark:border-blue-600 text-blue-700 dark:text-blue-400 rounded text-xs hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors flex items-center justify-center gap-1">
            <i class="fas fa-plus"></i> Agregar Sección
        </button>
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
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.reactivos', {{ json_encode(array_values(array_filter($props['reactivos'] ?? [], fn($r, $i) => $i !== $ri, ARRAY_FILTER_USE_BOTH))) }})"
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

    {{-- Info --}}
    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">
            <i class="fas fa-info-circle mr-1"></i> ¿Cómo funciona este componente?
        </p>
        <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-4 list-disc">
            <li>Diseñado para informes de <strong>citología</strong>. Incluye un selector de tipo de tumor y secciones dinámicas.</li>
            <li><strong>Sección Editable:</strong> El bioquímico podrá editar libremente el texto base (ej: observación macroscópica).</li>
            <li><strong>Sección Dependiente:</strong> El texto se carga automáticamente según el tumor seleccionado. Defina un texto diferente para cada tipo de tumor.</li>
            <li><strong>Sección Con Tumor:</strong> Muestra un texto fijo seguido del nombre del tumor seleccionado (ej: "Se confirma el diagnostico de <em>Histiositoma fibroso benigno</em>").</li>
            <li>Puede agregar o quitar secciones y tumores según la necesidad de cada plantilla.</li>
        </ul>
    </div>
</div>
