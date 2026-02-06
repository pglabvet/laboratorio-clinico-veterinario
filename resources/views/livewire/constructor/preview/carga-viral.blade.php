<!-- Preview de Carga Viral qPCR -->
<div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-800">
    {{-- Título principal --}}
    @if(isset($props['titulo']))
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-300 dark:border-zinc-700 py-3">
        <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-sm uppercase tracking-wide">{{ $props['titulo'] }}</h4>
        @if(!empty($props['nombre_completo']))
        <p class="text-gray-600 dark:text-zinc-400 text-center text-xs mt-1">{{ $props['nombre_completo'] }}</p>
        @endif
    </div>
    @endif

    <div class="p-4 space-y-4">
        {{-- Sección de campos del análisis (dinámicos) --}}
        <div class="grid grid-cols-1 gap-3">
            @if(!empty($props['campos']))
                @foreach($props['campos'] as $campo)
                <div class="flex items-center border border-gray-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                    <div class="bg-gray-100 dark:bg-zinc-900 px-4 py-2 font-semibold text-gray-700 dark:text-zinc-300 text-sm w-44 border-r border-gray-200 dark:border-zinc-700">
                        {{ $campo['etiqueta'] ?? 'CAMPO' }}:
                    </div>
                    <div class="flex-1 px-4 py-2 text-gray-400 dark:text-zinc-500 italic text-sm">
                        @if(($campo['tipo'] ?? 'texto') === 'select')
                            (DETECTADO/NO DETECTADO)
                        @elseif(($campo['tipo'] ?? 'texto') === 'numero_cientifico')
                            (valor) × 10<sup>{{ $props['umbral_exponente'] ?? 5 }}</sup> {{ $props['unidad'] ?? 'copias/ml' }}
                        @else
                            (a completar por bioquímico)
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                {{-- Campos por defecto si no hay ninguno configurado --}}
                <div class="flex items-center border border-gray-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                    <div class="bg-gray-100 dark:bg-zinc-900 px-4 py-2 font-semibold text-gray-700 dark:text-zinc-300 text-sm w-44 border-r border-gray-200 dark:border-zinc-700">
                        SIN CAMPOS:
                    </div>
                    <div class="flex-1 px-4 py-2 text-gray-400 dark:text-zinc-500 italic text-sm">
                        Agrega campos en el panel de propiedades
                    </div>
                </div>
            @endif
        </div>

        {{-- Sección de Interpretación de Resultados en columnas --}}
        <div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
            <div class="bg-gray-100 dark:bg-zinc-900 px-4 py-2 border-b border-gray-300 dark:border-zinc-700">
                <h5 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-sm">INTERPRETACIÓN DE RESULTADOS</h5>
            </div>
            
            <div class="p-3 grid grid-cols-3 gap-2 text-xs">
                {{-- No Detectado --}}
                <div class="border-l-3 border-green-500 pl-2 py-1" style="border-left-width: 3px;">
                    <p class="font-bold text-green-700 dark:text-green-400 text-xs">
                        {{ $props['patogeno'] ?? 'PATÓGENO' }} NO DETECTADO:
                    </p>
                    <p class="text-gray-600 dark:text-zinc-400 text-[10px] leading-tight mt-1">
                        {{ $props['interpretaciones']['no_detectado']['descripcion'] ?? 'Sin detección de ADN viral en la muestra.' }}
                    </p>
                </div>

                {{-- Infección Regresiva --}}
                <div class="border-l-3 border-yellow-500 pl-2 py-1" style="border-left-width: 3px;">
                    <p class="font-bold text-yellow-700 dark:text-yellow-400 text-xs">
                        {{ $props['patogeno'] ?? 'PATÓGENO' }} - REGRESIVA:
                    </p>
                    <p class="text-gray-600 dark:text-zinc-400 text-[10px] leading-tight mt-1">
                        {{ $props['interpretaciones']['regresivo']['descripcion'] ?? 'Carga viral baja, fase de resolución.' }}
                    </p>
                    <p class="text-gray-500 dark:text-zinc-500 text-[9px] mt-1">
                        ADN &lt; {{ $props['umbral_valor'] ?? 10 }} × 10<sup>{{ $props['umbral_exponente'] ?? 5 }}</sup>
                    </p>
                </div>

                {{-- Infección Progresiva --}}
                <div class="border-l-3 border-red-500 pl-2 py-1" style="border-left-width: 3px;">
                    <p class="font-bold text-red-700 dark:text-red-400 text-xs">
                        {{ $props['patogeno'] ?? 'PATÓGENO' }} - PROGRESIVA:
                    </p>
                    <p class="text-gray-600 dark:text-zinc-400 text-[10px] leading-tight mt-1">
                        {{ $props['interpretaciones']['progresivo']['descripcion'] ?? 'Carga viral alta, infección activa.' }}
                    </p>
                    <p class="text-gray-500 dark:text-zinc-500 text-[9px] mt-1">
                        ADN &gt; {{ $props['umbral_valor'] ?? 10 }} × 10<sup>{{ $props['umbral_exponente'] ?? 5 }}</sup>
                    </p>
                </div>
            </div>
        </div>

        {{-- Indicador de Gráfica (placeholder simple) --}}
        @if($props['mostrar_grafica'] ?? true)
        <div class="bg-gray-50 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg p-4">
            <div class="flex items-center justify-center gap-2 text-blue-600 dark:text-blue-400">
                <i class="fas fa-chart-line text-2xl"></i>
                <div class="text-sm">
                    <div class="font-semibold">{{ $props['patogeno'] ?? 'Patógeno' }} - Posición del Paciente Respecto al Umbral</div>
                    <div class="text-xs text-gray-500 dark:text-zinc-400">Se mostrará aquí al ingresar resultados</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
