<!-- Preview de Panel qPCR (Múltiples Patógenos) -->
<div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-800">

    {{-- Encabezado del panel --}}
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-300 dark:border-zinc-700 py-3">
        <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-sm uppercase tracking-wide">
            Panel qPCR — Múltiples Patógenos
        </h4>
        <p class="text-gray-500 dark:text-zinc-400 text-center text-xs mt-1">
            Cada enfermedad ocupa una hoja en el PDF
        </p>
    </div>

    @forelse($props['patogenos'] ?? [] as $pi => $patogeno)

    {{-- Separador entre patógenos --}}
    @if($pi > 0)
    <div class="border-t-2 border-dashed border-gray-300 dark:border-zinc-600"></div>
    @endif

    <div class="p-4 space-y-4">

        {{-- Título del patógeno --}}
        @if(!empty($patogeno['titulo']))
        <div class="bg-white dark:bg-zinc-800 py-1">
            <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-sm uppercase tracking-wide">{{ $patogeno['titulo'] }}</h4>
            @if(!empty($patogeno['nombre_completo']))
            <p class="text-gray-600 dark:text-zinc-400 text-center text-xs mt-1">{{ $patogeno['nombre_completo'] }}</p>
            @endif
        </div>
        @endif

        {{-- Campos del análisis --}}
        <div class="grid grid-cols-1 gap-3">
            @if(!empty($patogeno['campos']))
                @foreach($patogeno['campos'] as $campo)
                <div class="flex items-center border border-gray-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                    <div class="bg-gray-100 dark:bg-zinc-900 px-4 py-2 font-semibold text-gray-700 dark:text-zinc-300 text-sm w-44 border-r border-gray-200 dark:border-zinc-700">
                        {{ $campo['etiqueta'] ?? 'CAMPO' }}:
                    </div>
                    <div class="flex-1 px-4 py-2 text-gray-400 dark:text-zinc-500 italic text-sm">
                        @if(($campo['tipo'] ?? 'texto') === 'select')
                            (DETECTADO/NO DETECTADO)
                        @elseif(($campo['tipo'] ?? 'texto') === 'numero_cientifico')
                            (valor) × 10<sup>{{ $patogeno['umbral_exponente'] ?? 5 }}</sup> {{ $patogeno['unidad'] ?? 'copias/ml' }}
                        @else
                            (a completar por bioquímico)
                        @endif
                    </div>
                </div>
                @endforeach
            @else
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

        {{-- Sección de Interpretación de Resultados --}}
        <div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
            <div class="bg-gray-100 dark:bg-zinc-900 px-4 py-2 border-b border-gray-300 dark:border-zinc-700">
                <h5 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-sm">INTERPRETACIÓN DE RESULTADOS</h5>
            </div>

            <div class="p-3 grid grid-cols-3 gap-2 text-xs">
                {{-- No Detectado --}}
                <div class="border-l-3 border-green-500 pl-2 py-1" style="border-left-width: 3px;">
                    <p class="font-bold text-green-700 dark:text-green-400 text-xs">
                        {{ $patogeno['siglas'] ?? 'PATÓGENO' }} NO DETECTADO:
                    </p>
                    <p class="text-gray-600 dark:text-zinc-400 text-[10px] leading-tight mt-1">
                        {{ $patogeno['interpretaciones']['no_detectado']['descripcion'] ?? 'Sin detección de ADN viral en la muestra.' }}
                    </p>
                </div>

                {{-- Infección Regresiva --}}
                <div class="border-l-3 border-yellow-500 pl-2 py-1" style="border-left-width: 3px;">
                    <p class="font-bold text-yellow-700 dark:text-yellow-400 text-xs">
                        {{ $patogeno['siglas'] ?? 'PATÓGENO' }} - REGRESIVA:
                    </p>
                    <p class="text-gray-600 dark:text-zinc-400 text-[10px] leading-tight mt-1">
                        {{ $patogeno['interpretaciones']['regresivo']['descripcion'] ?? 'Carga viral baja, fase de resolución.' }}
                    </p>
                    <p class="text-gray-500 dark:text-zinc-500 text-[9px] mt-1">
                        ADN &lt; {{ $patogeno['umbral_valor'] ?? 10 }} × 10<sup>{{ $patogeno['umbral_exponente'] ?? 5 }}</sup>
                    </p>
                </div>

                {{-- Infección Progresiva --}}
                <div class="border-l-3 border-red-500 pl-2 py-1" style="border-left-width: 3px;">
                    <p class="font-bold text-red-700 dark:text-red-400 text-xs">
                        {{ $patogeno['siglas'] ?? 'PATÓGENO' }} - PROGRESIVA:
                    </p>
                    <p class="text-gray-600 dark:text-zinc-400 text-[10px] leading-tight mt-1">
                        {{ $patogeno['interpretaciones']['progresivo']['descripcion'] ?? 'Carga viral alta, infección activa.' }}
                    </p>
                    <p class="text-gray-500 dark:text-zinc-500 text-[9px] mt-1">
                        ADN &gt; {{ $patogeno['umbral_valor'] ?? 10 }} × 10<sup>{{ $patogeno['umbral_exponente'] ?? 5 }}</sup>
                    </p>
                </div>
            </div>
        </div>

        {{-- Indicador de Gráfica (placeholder) --}}
        @if($patogeno['mostrar_grafica'] ?? true)
        <div class="bg-gray-50 dark:bg-zinc-900 border border-gray-300 dark:border-zinc-700 rounded-lg p-4">
            <div class="flex items-center justify-center gap-2 text-blue-600 dark:text-blue-400">
                <i class="fas fa-chart-line text-2xl"></i>
                <div class="text-sm">
                    <div class="font-semibold">{{ $patogeno['siglas'] ?? 'Patógeno' }} - Posición del Paciente Respecto al Umbral</div>
                    <div class="text-xs text-gray-500 dark:text-zinc-400">Se mostrará aquí al ingresar resultados</div>
                </div>
            </div>
        </div>
        @endif

    </div>

    @empty
    <div class="p-6 text-center text-gray-400 dark:text-zinc-500 text-sm italic">
        Sin patógenos configurados. Usa el panel de propiedades →
    </div>
    @endforelse

</div>
