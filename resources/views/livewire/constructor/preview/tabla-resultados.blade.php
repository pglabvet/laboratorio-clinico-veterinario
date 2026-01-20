<!-- Preview de Tabla de Resultados -->
<div class="space-y-3">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center">{{ $props['titulo'] }}</h4>
    @endif
    
    @if(isset($props['descripcion']) && $props['descripcion'])
    <p class="text-sm text-gray-600 dark:text-zinc-400 text-center italic">{{ $props['descripcion'] }}</p>
    @endif

    <table class="w-full border border-gray-300 dark:border-zinc-700 text-sm">
        <thead>
            <tr class="bg-gray-100 dark:bg-zinc-900">
                @if(isset($props['columnas']))
                    @foreach($props['columnas'] as $columna)
                    <th class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-semibold text-gray-900 dark:text-zinc-100">
                        {{ $columna['nombre'] ?? 'COLUMNA' }}
                    </th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @if(isset($props['filas']) && count($props['filas']) > 0)
                @foreach($props['filas'] as $analisis)
                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50">
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 font-medium text-gray-900 dark:text-zinc-100">
                        {{ $analisis }}
                    </td>
                    @for($i = 1; $i < count($props['columnas']); $i++)
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-400 dark:text-zinc-500 italic text-xs">
                        (a completar)
                    </td>
                    @endfor
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ count($props['columnas'] ?? [3]) }}" class="border border-gray-300 dark:border-zinc-700 px-3 py-4 text-center text-gray-400 dark:text-zinc-500">
                        Agrega los nombres de análisis en las propiedades (ej: T4, T3, TSH...)
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
