<!-- Preview de Tabla Hematológica -->
<div class="overflow-x-auto">
    <table class="w-full border border-gray-300 text-xs">
        <thead>
            <tr>
                <th colspan="5" class="border border-gray-300 px-2 py-2 bg-purple-100 font-bold">{{ $props['titulo'] ?? 'CUADRO HEMÁTICO' }}</th>
                <th colspan="7" class="border border-gray-300 px-2 py-2 bg-purple-100"></th>
            </tr>
            <tr class="bg-gray-100 text-center text-xs">
                <th class="border border-gray-300 px-2 py-1" rowspan="2">CUADRO<br>HEMÁTICO</th>
                <th class="border border-gray-300 px-2 py-1" colspan="2"></th>
                <th class="border border-gray-300 px-2 py-1" colspan="2">VALORES DE REF</th>
                <th class="border border-gray-300 px-2 py-1" rowspan="2"></th>
                <th class="border border-gray-300 px-2 py-1" colspan="2">VALOR REL</th>
                <th class="border border-gray-300 px-2 py-1" rowspan="2">Val ref</th>
                <th class="border border-gray-300 px-2 py-1" colspan="2">VALOR ABS</th>
                <th class="border border-gray-300 px-2 py-1" rowspan="2">Val ref</th>
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
                    <td class="border border-gray-300 px-2 py-1 bg-purple-50 font-semibold text-xs">{{ $param['nombre'] }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-gray-400 italic text-xs">(valor)</td>
                    <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $param['unidad'] }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $param['ref_min'] }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $param['ref_max'] ?? '' }}</td>
                @else
                    <td class="border border-gray-300 px-2 py-1" colspan="5"></td>
                @endif
                
                @if($i < count($props['diferenciales'] ?? []))
                    @php $dif = $props['diferenciales'][$i]; @endphp
                    <td class="border border-gray-300 px-2 py-1 bg-purple-50 font-semibold text-xs">{{ $dif['nombre'] }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-gray-400 italic text-xs">(val)</td>
                    <td class="border border-gray-300 px-1 py-1 text-center text-xs">%</td>
                    <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $dif['ref_rel_min'] }}-{{ $dif['ref_rel_max'] }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-gray-400 italic text-xs">(val)</td>
                    <td class="border border-gray-300 px-1 py-1 text-center text-xs">mm³</td>
                    <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $dif['ref_abs_min'] }}-{{ $dif['ref_abs_max'] }}</td>
                @else
                    <td class="border border-gray-300 px-2 py-1" colspan="7"></td>
                @endif
            </tr>
            @endfor
            
            <!-- Índices eritrocitarios -->
            @foreach(($props['indices'] ?? []) as $index => $indice)
            <tr>
                @if($index === 0)
                <td class="border border-gray-300 px-2 py-1 bg-purple-50 font-semibold text-xs" rowspan="{{ count($props['indices'] ?? []) }}">INDICES<br>ERITROCIT.</td>
                @endif
                <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $indice['nombre'] }}</td>
                <td class="border border-gray-300 px-2 py-1 text-gray-400 italic text-xs">(valor)</td>
                <td class="border border-gray-300 px-2 py-1 text-center text-xs">{{ $indice['unidad'] }}</td>
                <td class="border border-gray-300 px-2 py-1 text-xs" colspan="2">{{ $indice['referencia'] }}</td>
                <td class="border border-gray-300" colspan="7"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
