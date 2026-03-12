{{-- Componente PDF: Examen Microscópico --}}
@php
    $filasPlantilla = $componente['propiedades']['filas'] ?? [];

    // Generar texto de rango desde datos estructurados
    $generarTextoRango = function ($fila) {
        $tipo = $fila['rango_tipo'] ?? 'min-max';
        $unidad = $fila['unidad'] ?? '';
        $sufijo = $unidad ? ' ' . $unidad : '';
        return match($tipo) {
            'min-max' => (!empty($fila['rango_min']) || !empty($fila['rango_max']))
                ? ($fila['rango_min'] ?? '') . ' - ' . ($fila['rango_max'] ?? '') . $sufijo
                : '',
            'menor' => !empty($fila['rango_valor']) ? '< ' . $fila['rango_valor'] . $sufijo : '',
            'menor-igual' => !empty($fila['rango_valor']) ? '<= ' . $fila['rango_valor'] . $sufijo : '',
            'mayor' => !empty($fila['rango_valor']) ? '> ' . $fila['rango_valor'] . $sufijo : '',
            'mayor-igual' => !empty($fila['rango_valor']) ? '>= ' . $fila['rango_valor'] . $sufijo : '',
            default => '',
        };
    };

    // Indexar filas de plantilla por nombre para búsqueda rápida
    $filasPlantillaByName = collect($filasPlantilla)->keyBy('parametro');

    $tieneRangos = collect($filasPlantilla)->contains(fn($f) => $generarTextoRango($f) !== '');
@endphp

@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="text-align: left; width: {{ $tieneRangos ? '35%' : '40%' }};">{{ $componente['propiedades']['columna_parametro'] ?? 'PARÁMETRO' }}</th>
            <th style="text-align: center;">{{ $componente['propiedades']['columna_resultado'] ?? 'RESULTADO' }}</th>
            @if($tieneRangos)
            <th style="text-align: center; width: 20%;">{{ $componente['propiedades']['columna_rango'] ?? 'RANGO REF.' }}</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(is_array($fila) && !empty($fila['resultado']))
            @php
                $estiloResultado = '';
                $rangoTexto = '';

                // Buscar la fila de plantilla correspondiente por nombre de parámetro
                $filaTemplate = $filasPlantillaByName->get($fila['parametro'] ?? '');

                if ($filaTemplate) {
                    $rangoTexto = $generarTextoRango($filaTemplate);
                    $clasificacion = 'normal';

                    if ($fila['resultado'] !== '' && is_numeric($fila['resultado'])) {
                        $res = floatval($fila['resultado']);
                        $rtipo = $filaTemplate['rango_tipo'] ?? 'min-max';

                        if ($rtipo === 'min-max') {
                            $min = is_numeric($filaTemplate['rango_min'] ?? '') ? floatval($filaTemplate['rango_min']) : null;
                            $max = is_numeric($filaTemplate['rango_max'] ?? '') ? floatval($filaTemplate['rango_max']) : null;
                            if ($min !== null || $max !== null) {
                                $amplitud = ($min !== null && $max !== null) ? $max - $min : 0;
                                $umbral = $amplitud * 0.15;
                                if ($min !== null && $res < $min) {
                                    $clasificacion = ($amplitud > 0 && $res >= $min - $umbral) ? 'alerta' : 'critico';
                                } elseif ($max !== null && $res > $max) {
                                    $clasificacion = ($amplitud > 0 && $res <= $max + $umbral) ? 'alerta' : 'critico';
                                }
                            }
                        } elseif (is_numeric($filaTemplate['rango_valor'] ?? '')) {
                            $val = floatval($filaTemplate['rango_valor']);
                            $umbral = abs($val) * 0.15;
                            $fuera = match($rtipo) {
                                'menor' => $res >= $val,
                                'menor-igual' => $res > $val,
                                'mayor' => $res <= $val,
                                'mayor-igual' => $res < $val,
                                default => false,
                            };
                            if ($fuera) {
                                $dist = match($rtipo) {
                                    'menor', 'menor-igual' => $res - $val,
                                    'mayor', 'mayor-igual' => $val - $res,
                                    default => 0,
                                };
                                $clasificacion = $dist <= $umbral ? 'alerta' : 'critico';
                            }
                        }
                    }

                    $estiloResultado = match($clasificacion) {
                        'alerta' => 'color: #2563eb; font-weight: bold;',
                        'critico' => 'color: #dc2626; font-weight: bold;',
                        default => '',
                    };
                }
            @endphp
            <tr>
                <td style="font-weight: bold;">
                    {{ $fila['parametro'] ?? '' }}
                </td>
                <td style="text-align: center; {{ $estiloResultado }}">
                    {{ $fila['resultado'] ?? '' }}
                </td>
                @if($tieneRangos)
                <td style="text-align: center; font-size: 9px; color: #718096;">
                    {{ $rangoTexto }}
                </td>
                @endif
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
