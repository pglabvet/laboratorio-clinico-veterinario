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
                        {{ is_array($analisis) ? ($analisis['nombre'] ?? '') : $analisis }}
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-400 dark:text-zinc-500 italic text-xs">
                        (a completar)
                    </td>
                    <td class="border border-gray-300 dark:border-zinc-700 px-3 py-2 text-gray-600 dark:text-zinc-400 text-xs">
                        @php
                            $rangoTipo = is_array($analisis) ? ($analisis['rango_tipo'] ?? '') : '';
                            $unidad = is_array($analisis) ? ($analisis['unidad'] ?? '') : '';
                            $rangoDisplay = '';
                            $rangosMultiples = [];

                            if ($rangoTipo === 'min-max') {
                                $min = is_array($analisis) ? ($analisis['rango_min'] ?? '') : '';
                                $max = is_array($analisis) ? ($analisis['rango_max'] ?? '') : '';
                                $rangoDisplay = ($min !== '' && $max !== '') ? "$min - $max" : '';
                            } elseif (in_array($rangoTipo, ['menor', 'menor-igual', 'mayor', 'mayor-igual'])) {
                                $val = is_array($analisis) ? ($analisis['rango_valor'] ?? '') : '';
                                $prefijo = match($rangoTipo) { 'menor' => '<', 'menor-igual' => '<=', 'mayor' => '>', 'mayor-igual' => '>=' };
                                $rangoDisplay = $val !== '' ? "$prefijo $val" : '';
                            } elseif ($rangoTipo === 'multiple') {
                                $rangosArr = is_array($analisis) ? ($analisis['rangos'] ?? []) : [];
                                $unidadR = is_array($analisis) ? ($analisis['unidad'] ?? '') : '';
                                if (!empty($rangosArr)) {
                                    foreach ($rangosArr as $re) {
                                        $t = $re['tipo'] ?? 'min-max';
                                        $str = match($t) {
                                            'min-max' => ($re['min'] ?? '') . ' - ' . ($re['max'] ?? ''),
                                            'menor' => '< ' . ($re['valor'] ?? ''),
                                            'menor-igual' => '<= ' . ($re['valor'] ?? ''),
                                            'mayor' => '> ' . ($re['valor'] ?? ''),
                                            'mayor-igual' => '>= ' . ($re['valor'] ?? ''),
                                            default => '',
                                        };
                                        $parts = array_filter([$str, $unidadR, $re['etiqueta'] ?? '']);
                                        $rangosMultiples[] = implode(' ', $parts);
                                    }
                                } else {
                                    $ref = is_array($analisis) ? ($analisis['rango_ref'] ?? '') : '';
                                    $rangosMultiples = array_filter(explode("\n", $ref), fn($r) => trim($r) !== '');
                                }
                            } else {
                                $ref = is_array($analisis) ? ($analisis['rango_ref'] ?? '') : '';
                                $lines = is_string($ref) ? array_filter(explode("\n", $ref), fn($r) => trim($r) !== '') : [];
                                if (count($lines) > 1) { $rangosMultiples = $lines; }
                                elseif (count($lines) === 1) { $rangoDisplay = trim($lines[0]); }
                            }
                        @endphp
                        @if(count($rangosMultiples) > 0)
                            <div class="inline-block text-left">
                            @foreach($rangosMultiples as $r)
                                <div>{{ trim($r) }}</div>
                            @endforeach
                            </div>
                        @elseif($rangoDisplay)
                            {{ $rangoDisplay }}
                            @if($unidad)
                                <span class="text-gray-500 dark:text-zinc-500 ml-2">{{ $unidad }}</span>
                            @endif
                        @else
                            (a completar)
                        @endif
                    </td>
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
