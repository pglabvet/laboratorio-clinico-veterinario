{{-- Componente PDF V2: Examen Diferencial (datos sueltos) --}}
@php
    $filasPlantilla = $componente['propiedades']['filas'] ?? [];

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

    $filasPlantillaByName = collect($filasPlantilla)->keyBy('nombre');

    $tieneRangos = collect($filasPlantilla)->contains(function ($f) use ($generarTextoRango) {
        return ($f['tipo_fila'] ?? '3col') === '3col' && $generarTextoRango($f) !== '';
    });
@endphp

@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="text-align: left; width: {{ $tieneRangos ? '35%' : '40%' }};">{{ $componente['propiedades']['columna_analisis'] ?? 'ANÁLISIS' }}</th>
            <th style="text-align: center;">{{ $componente['propiedades']['columna_resultado'] ?? 'RESULTADO' }}</th>
            @if($tieneRangos)
            <th style="text-align: center; width: 20%;">{{ $componente['propiedades']['columna_rango'] ?? 'RANGO REF.' }}</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(is_array($fila) && isset($fila['resultado']) && $fila['resultado'] !== '' && $fila['resultado'] !== null)
            @php
                $tipoFila = $fila['tipo_fila'] ?? '3col';
                $claseColor = 'resultado-normal';
                $rangoTexto = '';

                $filaTemplate = $filasPlantillaByName->get($fila['nombre'] ?? '');

                if ($filaTemplate && $tipoFila === '3col') {
                    $rangoTexto = $generarTextoRango($filaTemplate);
                    $clasificacion = 'normal';

                    if ($fila['resultado'] !== '' && is_numeric($fila['resultado'])) {
                        $res = floatval($fila['resultado']);
                        $rtipo = $filaTemplate['rango_tipo'] ?? 'min-max';

                        if ($rtipo === 'min-max') {
                            $min = is_numeric($filaTemplate['rango_min'] ?? '') ? floatval($filaTemplate['rango_min']) : null;
                            $max = is_numeric($filaTemplate['rango_max'] ?? '') ? floatval($filaTemplate['rango_max']) : null;
                            if ($min !== null || $max !== null) {
                                if ($min !== null && $res < $min) {
                                    $clasificacion = 'bajo';
                                } elseif ($max !== null && $res > $max) {
                                    $clasificacion = 'alto';
                                }
                            }
                        } elseif (is_numeric($filaTemplate['rango_valor'] ?? '')) {
                            $val = floatval($filaTemplate['rango_valor']);
                            $fuera = match($rtipo) {
                                'menor' => $res >= $val,
                                'menor-igual' => $res > $val,
                                'mayor' => $res <= $val,
                                'mayor-igual' => $res < $val,
                                default => false,
                            };
                            if ($fuera) {
                                $clasificacion = match($rtipo) {
                                    'menor', 'menor-igual' => 'alto',
                                    'mayor', 'mayor-igual' => 'bajo',
                                    default => 'normal',
                                };
                            }
                        }
                    }

                    $claseColor = match($clasificacion) {
                        'bajo' => 'resultado-alerta',
                        'alto' => 'resultado-critico',
                        default => 'resultado-normal',
                    };
                }
            @endphp
            <tr>
                <td>{{ $fila['nombre'] ?? '' }}</td>
                @if($tipoFila === '2col')
                    <td style="text-align: center;">{{ $fila['resultado'] ?? '' }}</td>
                    @if($tieneRangos)
                    <td></td>
                    @endif
                @else
                    <td style="text-align: center;" class="{{ $claseColor }}">{{ $fila['resultado'] ?? '' }}</td>
                    @if($tieneRangos)
                    <td style="text-align: center;" class="ref-text">{{ $rangoTexto }}</td>
                    @endif
                @endif
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
