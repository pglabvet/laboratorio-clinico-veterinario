<!-- Propiedades de Carga Viral qPCR -->
<div class="space-y-4">
    <div class="text-xs text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-2 rounded mb-2">
        Índice: {{ $indiceComponente }} | ID: {{ $componenteId ?? "N/A" }}
    </div>

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

    {{-- Información de ayuda --}}
    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
        <p class="text-xs text-blue-800 dark:text-blue-300">
            <i class="fas fa-info-circle mr-1"></i>
            <strong>El bioquímico completará los valores de cada campo definido.</strong>
        </p>
    </div>
</div>
