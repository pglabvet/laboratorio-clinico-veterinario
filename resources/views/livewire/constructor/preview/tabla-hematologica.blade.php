<!-- Preview de Tabla Hematológica -->
<div class="overflow-x-auto">
    <table class="w-full border border-gray-300 dark:border-zinc-700 text-xs">
        <thead>
            <tr>
                <th colspan="5" class="border border-gray-300 dark:border-zinc-700 px-2 py-2 bg-purple-100 dark:bg-purple-900/30 font-bold text-gray-900 dark:text-zinc-100">{{ $props['titulo'] ?? 'CUADRO HEMÁTICO' }}</th>
                <th colspan="7" class="border border-gray-300 dark:border-zinc-700 px-2 py-2 bg-purple-100 dark:bg-purple-900/30"></th>
            </tr>
            <tr class="bg-gray-100 dark:bg-zinc-900 text-center text-xs">
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" rowspan="2">CUADRO<br>HEMÁTICO</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1" colspan="2"></th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" colspan="2">VALORES DE REF</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1" rowspan="2"></th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" colspan="2">VALOR REL</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" rowspan="2">Val ref</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" colspan="2">VALOR ABS</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100" rowspan="2">Val ref</th>
            </tr>
        </thead>
        <tbody>
            @php
                $maxRows = max(count($props['parametros_principales'] ?? []), count($props['diferenciales'] ?? []));
            @endphp
            
            @for($i = 0; $i < $maxRows; $i++)
            <tr>
                @if($i < count($props['parametros_principales'] ?? []))
                    @php $param = $props['parametros_principales'][$i]; @endphp
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 font-semibold text-xs text-gray-900 dark:text-zinc-100">{{ $param['nombre'] }}</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-400 dark:text-zinc-500 italic text-xs">(valor)</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $param['unidad'] }}</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $param['ref_min'] }}</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $param['ref_max'] ?? '' }}</td>
                @else
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1" colspan="5"></td>
                @endif
                
                @if($i < count($props['diferenciales'] ?? []))
                    @php $dif = $props['diferenciales'][$i]; @endphp
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 font-semibold text-xs text-gray-900 dark:text-zinc-100">{{ $dif['nombre'] }}</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-400 dark:text-zinc-500 italic text-xs">(val)</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">%</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $dif['ref_rel_min'] }}-{{ $dif['ref_rel_max'] }}</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-400 dark:text-zinc-500 italic text-xs">(val)</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">mm³</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $dif['ref_abs_min'] }}-{{ $dif['ref_abs_max'] }}</td>
                @else
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1" colspan="7"></td>
                @endif
            </tr>
            @endfor
            
            <!-- Índices eritrocitarios -->
            @foreach(($props['indices'] ?? []) as $index => $indice)
            <tr>
                @if($index === 0)
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 font-semibold text-xs text-gray-900 dark:text-zinc-100" rowspan="{{ count($props['indices'] ?? []) }}">INDICES<br>ERITROCIT.</td>
                @endif
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $indice['nombre'] }}</td>
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-400 dark:text-zinc-500 italic text-xs">(valor)</td>
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $indice['unidad'] }}</td>
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-xs text-gray-900 dark:text-zinc-100" colspan="2">{{ $indice['referencia'] }}</td>
                <td class="border border-gray-300 dark:border-zinc-700" colspan="7"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
