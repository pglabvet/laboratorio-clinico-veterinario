<!-- Preview de Tabla Temporal con Gráfica -->
<div class="border border-gray-300 dark:border-zinc-700 rounded-lg overflow-hidden">
    @if(isset($props['titulo']))
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-300 dark:border-zinc-700">
        <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center py-2">{{ $props['titulo'] }}</h4>
    </div>
    @endif

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-100 dark:bg-zinc-900">
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 bg-yellow-100 dark:bg-yellow-900/20">Análisis</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 bg-green-100 dark:bg-green-900/20">Hora</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 bg-green-100 dark:bg-green-900/20">Resultado</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-900 dark:text-zinc-100 bg-yellow-100 dark:bg-yellow-900/20">Rangos de referencia</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($props['filas']))
                @foreach($props['filas'] as $fila)
                <tr>
                    <!-- Análisis (amarillo - admin) -->
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold bg-yellow-50 dark:bg-yellow-900/10 text-gray-900 dark:text-zinc-100">
                        {{ $fila['analisis'] ?: 'Sin nombre' }}
                    </td>
                    
                    <!-- Hora (verde - usuario) -->
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-green-50 dark:bg-green-900/10 text-gray-400 dark:text-zinc-500 italic text-center">
                        (a completar)
                    </td>
                    
                    <!-- Resultado (verde - usuario) -->
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-green-50 dark:bg-green-900/10 text-gray-400 dark:text-zinc-500 italic text-center">
                        (a completar)
                    </td>
                    
                    <!-- Rangos de referencia (amarillo - admin) -->
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 bg-yellow-50 dark:bg-yellow-900/10 text-gray-900 dark:text-zinc-100 text-center">
                        {{ $fila['rango_referencia'] ?: 'Sin rango' }}
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="border border-gray-300 dark:border-zinc-700 px-3 py-4 text-center text-gray-400 dark:text-zinc-500 italic">
                        No hay análisis configurados. Haz clic en "Agregar Análisis" en el panel de propiedades.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Indicador de gráfica -->
    @if(($props['mostrar_grafica'] ?? true))
    <div class="bg-gray-50 dark:bg-zinc-900 border-t border-gray-300 dark:border-zinc-700 p-4">
        <div class="flex items-center justify-center gap-2 text-blue-600 dark:text-blue-400">
            <i class="fas fa-chart-line text-2xl"></i>
            <div class="text-sm">
                <div class="font-semibold">Gráfica de Líneas</div>
                <div class="text-xs text-gray-500 dark:text-zinc-400">Se mostrará aquí al ingresar resultados</div>
            </div>
        </div>
    </div>
    @endif
</div>
