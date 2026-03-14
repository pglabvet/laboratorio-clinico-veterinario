{{-- Componente PDF V2: Tabla Temporal con Gráfica --}}
@php
    $generarTextoRango = function ($fila) {
        $tipo = $fila['rango_tipo'] ?? 'min-max';
        $unidad = $fila['unidad'] ?? '';
        $sufijo = $unidad ? " $unidad" : '';
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

    $clasificarResultado = function ($resultadoNumerico, $fila) {
        $tipo = $fila['rango_tipo'] ?? '';
        if (!$tipo) return 'normal';

        if ($tipo === 'min-max') {
            if (empty($fila['rango_min']) && empty($fila['rango_max'])) return 'normal';
            $min = !empty($fila['rango_min']) ? floatval($fila['rango_min']) : null;
            $max = !empty($fila['rango_max']) ? floatval($fila['rango_max']) : null;
            $amplitud = ($min !== null && $max !== null) ? $max - $min : 0;
            $umbral = $amplitud * 0.15;
            if ($min !== null && $resultadoNumerico < $min) {
                return ($amplitud > 0 && $resultadoNumerico >= $min - $umbral) ? 'alerta' : 'critico';
            }
            if ($max !== null && $resultadoNumerico > $max) {
                return ($amplitud > 0 && $resultadoNumerico <= $max + $umbral) ? 'alerta' : 'critico';
            }
            return 'normal';
        }

        if (empty($fila['rango_valor'])) return 'normal';
        $val = floatval($fila['rango_valor']);
        $umbral = abs($val) * 0.15;
        $fuera = match($tipo) {
            'menor' => $resultadoNumerico >= $val,
            'menor-igual' => $resultadoNumerico > $val,
            'mayor' => $resultadoNumerico <= $val,
            'mayor-igual' => $resultadoNumerico < $val,
            default => false,
        };
        if (!$fuera) return 'normal';
        $dist = match($tipo) {
            'menor', 'menor-igual' => $resultadoNumerico - $val,
            'mayor', 'mayor-igual' => $val - $resultadoNumerico,
            default => 0,
        };
        return $dist <= $umbral ? 'alerta' : 'critico';
    };

    $filasPlantilla = $componente['propiedades']['filas'] ?? [];
@endphp

@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="width: 30%;">Análisis</th>
            <th style="width: 15%; text-align: center;">Hora</th>
            <th style="width: 20%; text-align: center;">Resultado</th>
            <th style="width: 35%; text-align: center;">Rangos de referencia</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $filaIdx => $fila)
            @if(is_array($fila))
            @php
                $filaTemplate = $filasPlantilla[$filaIdx] ?? [];
                $clasificacion = 'normal';
                $resultadoNumerico = isset($fila['resultado']) ? floatval($fila['resultado']) : null;
                
                if ($resultadoNumerico !== null) {
                    $clasificacion = $clasificarResultado($resultadoNumerico, $filaTemplate);
                }

                $rangoTexto = $generarTextoRango($filaTemplate);
                $claseColor = match($clasificacion) {
                    'alerta' => 'resultado-alerta',
                    'critico' => 'resultado-critico',
                    default => 'resultado-normal',
                };
            @endphp
            <tr>
                <td>
                    {{ $fila['analisis'] ?? '' }}
                </td>
                <td style="text-align: center;">
                    {{ $fila['hora'] ?? '' }}
                </td>
                <td style="text-align: center; font-weight: bold;" class="{{ $claseColor }}">
                    {{ $fila['resultado'] ?? 'N/A' }}
                </td>
                <td style="text-align: center;" class="ref-text">
                    {{ $rangoTexto }}
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>

{{-- Gráfica --}}
@if(($componente['propiedades']['mostrar_grafica'] ?? true))
    @if(isset($chartImage) && $chartImage)
        <div style="margin-top: 12px; text-align: center;">
            <div style="font-weight: bold; font-size: 11px; margin-bottom: 8px; color: #1e3a5f;">
                Gráfica de Resultados - {{ $componente['propiedades']['unidad_medida'] ?? 'ug/dL' }}
            </div>
            <div style="width: 100%; height: 150px; overflow: hidden;">
                <img src="{{ $chartImage }}" style="width: 100%; height: 150px;" alt="Gráfica de resultados">
            </div>
        </div>
    @endif
@endif

@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
