<!-- Propiedades de Carga Viral qPCR -->
<div class="space-y-4">
    {{-- Título Principal --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
            <i class="fas fa-heading mr-1 text-blue-500"></i> Título del Análisis
        </label>
        <input 
            type="text" 
            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.titulo"
            placeholder="Ej: DETECCIÓN POR qPCR EN TIEMPO REAL"
            class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
    </div>

    {{-- Información del Patógeno --}}
    <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-900">
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-3">
            <i class="fas fa-virus mr-1 text-red-500"></i> Información del Patógeno
        </label>
        
        <div class="space-y-3">
            {{-- Código/Siglas del patógeno --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Siglas del Patógeno</label>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.patogeno"
                    placeholder="Ej: FeLV, FIV, Parvo"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">Código corto que se usará en las etiquetas</p>
            </div>

            {{-- Nombre completo --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Nombre Completo</label>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.nombre_completo"
                    placeholder="Ej: Virus de la Leucemia Felina"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            </div>
        </div>
    </div>

    {{-- Campos del análisis (editables) --}}
    <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-900">
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
            <i class="fas fa-list mr-1 text-green-500"></i> Campos del Análisis
        </label>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mb-3">Define los campos que se mostrarán. El bioquímico completará los valores.</p>
        
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @foreach($props['campos'] ?? [] as $index => $campo)
            <div class="p-3 bg-white dark:bg-zinc-800 rounded border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600 dark:text-zinc-400">Campo {{ $index + 1 }}</span>
                    <button 
                        wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_values(array_filter($props['campos'], fn($f, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH))) }})"
                        class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 rounded text-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="space-y-2">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Etiqueta del Campo</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.etiqueta"
                            placeholder="Ej: MUESTRA ANALIZADA, RESULTADO..."
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                    </div>
                    
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-zinc-400 mb-1">Tipo de Campo</label>
                        <select 
                            wire:model.live="componentes.{{ $indiceComponente }}.propiedades.campos.{{ $index }}.tipo"
                            class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                            <option value="texto">Texto libre</option>
                            <option value="select">Selección (Detectado/No Detectado)</option>
                            <option value="numero_cientifico">Número con notación científica</option>
                        </select>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <flux:button 
            wire:click="$set('componentes.{{ $indiceComponente }}.propiedades.campos', {{ json_encode(array_merge($props['campos'] ?? [], [['etiqueta' => '', 'tipo' => 'texto']])) }})"
            variant="primary" 
            icon="plus" 
            class="w-full mt-3">
            Agregar Campo
        </flux:button>
    </div>

    {{-- Configuración del Umbral --}}
    <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-900">
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-3">
            <i class="fas fa-sliders-h mr-1 text-yellow-500"></i> Umbral de Referencia
        </label>
        
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                {{-- Valor del umbral --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Valor Base</label>
                    <input 
                        type="number"
                        step="0.01"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.umbral_valor"
                        placeholder="10"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                </div>

                {{-- Exponente --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Exponente (10^x)</label>
                    <input 
                        type="number"
                        wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.umbral_exponente"
                        placeholder="5"
                        class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
                </div>
            </div>

            {{-- Unidad de medida --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Unidad de Medida</label>
                <input 
                    type="text"
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.unidad"
                    placeholder="Ej: copias/ml"
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100">
            </div>

            {{-- Vista previa del umbral --}}
            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                <p class="text-xs text-blue-800 dark:text-blue-300 text-center">
                    <i class="fas fa-eye mr-1"></i>
                    Umbral: <strong>{{ $props['umbral_valor'] ?? 10 }} × 10<sup>{{ $props['umbral_exponente'] ?? 5 }}</sup></strong> {{ $props['unidad'] ?? 'copias/ml' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Interpretaciones --}}
    <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-900">
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-3">
            <i class="fas fa-clipboard-list mr-1 text-purple-500"></i> Textos de Interpretación
        </label>
        
        <div class="space-y-4">
            {{-- No Detectado --}}
            <div class="border-l-4 border-green-500 pl-3">
                <label class="block text-xs font-bold text-green-700 dark:text-green-400 mb-1">
                    NO DETECTADO
                </label>
                <textarea 
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.interpretaciones.no_detectado.descripcion"
                    rows="2"
                    placeholder="Sin detección de ADN viral en la muestra analizada."
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></textarea>
            </div>

            {{-- Infección Regresiva --}}
            <div class="border-l-4 border-yellow-500 pl-3">
                <label class="block text-xs font-bold text-yellow-700 dark:text-yellow-400 mb-1">
                    INFECCIÓN REGRESIVA (bajo umbral)
                </label>
                <textarea 
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.interpretaciones.regresivo.descripcion"
                    rows="2"
                    placeholder="Carga viral baja, posible infección en fase de resolución."
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></textarea>
            </div>

            {{-- Infección Progresiva --}}
            <div class="border-l-4 border-red-500 pl-3">
                <label class="block text-xs font-bold text-red-700 dark:text-red-400 mb-1">
                    INFECCIÓN PROGRESIVA (sobre umbral)
                </label>
                <textarea 
                    wire:model.live.debounce.500ms="componentes.{{ $indiceComponente }}.propiedades.interpretaciones.progresivo.descripcion"
                    rows="2"
                    placeholder="Carga viral alta, infección activa y progresiva."
                    class="w-full px-2 py-1 border border-gray-300 dark:border-zinc-700 rounded text-xs bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100"></textarea>
            </div>
        </div>
    </div>

    {{-- Configuración de Gráfica --}}
    <div class="border border-gray-300 dark:border-zinc-700 rounded-lg p-3 bg-gray-50 dark:bg-zinc-900">
        <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2">
            <i class="fas fa-chart-line mr-1 text-blue-500"></i> Configuración de Gráfica
        </label>
        
        <div class="flex items-center gap-2">
            <input 
                type="checkbox" 
                id="mostrar-grafica-{{ $componenteId }}"
                wire:model.live="componentes.{{ $indiceComponente }}.propiedades.mostrar_grafica"
                class="rounded border-gray-300 dark:border-zinc-700 text-blue-600">
            <label for="mostrar-grafica-{{ $componenteId }}" class="text-sm text-gray-700 dark:text-zinc-300">
                Mostrar gráfica de posición del paciente
            </label>
        </div>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-2">
            La gráfica mostrará una línea numérica con el umbral y la posición del resultado del paciente.
        </p>
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
            <li>Diseñado para reportes de <strong>PCR en tiempo real</strong> (qPCR). Incluye campos del análisis, umbral de referencia e interpretaciones automáticas.</li>
            <li>Los campos que definas arriba aparecerán como formulario para el bioquímico (ej: muestra analizada, resultado, técnica utilizada).</li>
            <li>El <strong>umbral</strong> (ej: 10×10⁵ copias/ml) se usa para clasificar automáticamente el resultado como No Detectado, Infección Regresiva o Progresiva.</li>
            <li>La <strong>gráfica</strong> (opcional) muestra visualmente la posición del resultado del paciente respecto al umbral.</li>
            <li>Los textos de interpretación se imprimen automáticamente en el PDF según el resultado ingresado.</li>
        </ul>
    </div>
</div>
