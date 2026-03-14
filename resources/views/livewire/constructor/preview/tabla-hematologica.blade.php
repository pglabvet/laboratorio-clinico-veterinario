<!-- Preview de Tabla Hematológica -->
@php
    // Generar texto de rango desde datos estructurados (con fallback a campos antiguos)
    $generarTextoRango = function ($item, $infijo = '') {
        $tipo = $item['rango_' . $infijo . 'tipo'] ?? 'min-max';
        $min = $item['rango_' . $infijo . 'min'] ?? $item['ref_' . $infijo . 'min'] ?? '';
        $max = $item['rango_' . $infijo . 'max'] ?? $item['ref_' . $infijo . 'max'] ?? '';
        $valor = $item['rango_' . $infijo . 'valor'] ?? '';
        return match($tipo) {
            'min-max' => (!empty($min) || !empty($max)) ? $min . ' - ' . $max : '',
            'menor' => !empty($valor) ? '< ' . $valor : '',
            'menor-igual' => !empty($valor) ? '≤ ' . $valor : '',
            'mayor' => !empty($valor) ? '> ' . $valor : '',
            'mayor-igual' => !empty($valor) ? '≥ ' . $valor : '',
            default => '',
        };
    };
@endphp
<div class="overflow-x-auto">
    <table class="w-full border border-gray-300 dark:border-zinc-700 text-xs">
        <thead>
            <tr>
                <th colspan="3" class="border border-gray-300 dark:border-zinc-700 px-2 py-2 font-bold text-gray-900 dark:text-zinc-100">{{ $props['titulo'] ?? 'CUADRO HEMÁTICO' }}</th>
                <th colspan="5" class="border border-gray-300 dark:border-zinc-700 px-2 py-2"></th>
            </tr>
            <tr class="bg-gray-100 dark:bg-zinc-900 text-center text-xs">
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">CUADRO<br>HEMÁTICO</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1" colspan="1"></th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">VALORES DE REF</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1" rowspan="1"></th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">VALOR REL</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">Val ref</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">VALOR ABS</th>
                <th class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-900 dark:text-zinc-100">Val ref</th>
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
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 font-semibold text-xs text-gray-900 dark:text-zinc-100">{{ $param['nombre'] }}</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-400 dark:text-zinc-500 italic text-xs">(valor)</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-1 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $generarTextoRango($param) }}@if($param['unidad']) {{ $param['unidad'] }}@endif</td>
                @else
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1" colspan="3"></td>
                @endif
                
                @if($i < count($props['diferenciales'] ?? []))
                    @php $dif = $props['diferenciales'][$i]; @endphp
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 font-semibold text-xs text-gray-900 dark:text-zinc-100">{{ $dif['nombre'] }}</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-400 dark:text-zinc-500 italic text-xs">(val)</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $generarTextoRango($dif, 'rel_') }} %</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-400 dark:text-zinc-500 italic text-xs">(val)</td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $generarTextoRango($dif, 'abs_') }} mm³</td>
                @else
                    <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1" colspan="5"></td>
                @endif
            </tr>
            @endfor
            
            <!-- Índices eritrocitarios -->
            @foreach(($props['indices'] ?? []) as $index => $indice)
            <tr>
                @if($index === 0)
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 font-semibold text-xs text-gray-900 dark:text-zinc-100" rowspan="{{ count($props['indices'] ?? []) }}">INDICES<br>ERITROCIT.</td>
                @endif
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $indice['nombre'] }}</td>
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-gray-400 dark:text-zinc-500 italic text-xs">(valor)</td>
                <td class="border border-gray-300 dark:border-zinc-700 px-2 py-1 text-center text-xs text-gray-900 dark:text-zinc-100">{{ $generarTextoRango($indice) ?: ($indice['referencia'] ?? '') }}@if($indice['unidad']) {{ $indice['unidad'] }}@endif</td>
                <td class="border border-gray-300 dark:border-zinc-700" colspan="5"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
