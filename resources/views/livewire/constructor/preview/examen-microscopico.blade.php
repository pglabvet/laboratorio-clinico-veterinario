<!-- Preview de Examen Microscópico -->
@php
    $filas = $props['filas'] ?? [];
    $tieneRangos = collect($filas)->contains(fn($f) => !empty($f['rango_referencia']));
@endphp

<div class="space-y-3 p-4 border border-gray-300 dark:border-zinc-700 rounded-lg bg-gray-50 dark:bg-zinc-900">
    @if(isset($props['titulo']))
    <h4 class="font-bold text-gray-800 dark:text-zinc-100 text-center text-lg">{{ $props['titulo'] }}</h4>
    @endif

    @if(count($filas) > 0)
    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-200 dark:bg-zinc-700">
                <th class="border border-gray-300 dark:border-zinc-600 px-3 py-2 text-left font-semibold text-gray-700 dark:text-zinc-300">{{ $props['columna_parametro'] ?? 'PARÁMETRO' }}</th>
                <th class="border border-gray-300 dark:border-zinc-600 px-3 py-2 text-center font-semibold text-gray-700 dark:text-zinc-300">{{ $props['columna_resultado'] ?? 'RESULTADO' }}</th>
                @if($tieneRangos)
                <th class="border border-gray-300 dark:border-zinc-600 px-3 py-2 text-center font-semibold text-gray-700 dark:text-zinc-300">{{ $props['columna_rango'] ?? 'RANGO REF.' }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $fila)
                @if(!empty($fila['parametro']))
                <tr>
                    <td class="border border-gray-300 dark:border-zinc-600 px-3 py-2 font-semibold text-gray-700 dark:text-zinc-300">{{ $fila['parametro'] }}</td>
                    <td class="border border-gray-300 dark:border-zinc-600 px-3 py-2 text-center text-gray-400 dark:text-zinc-500 italic">(a completar)</td>
                    @if($tieneRangos)
                    <td class="border border-gray-300 dark:border-zinc-600 px-3 py-2 text-center text-gray-500 dark:text-zinc-400 text-xs">{{ $fila['rango_referencia'] ?? '' }}</td>
                    @endif
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @else
        <p class="text-sm text-gray-400 dark:text-zinc-500 italic text-center py-4">
            Agrega parámetros en las propiedades
        </p>
    @endif
</div>
