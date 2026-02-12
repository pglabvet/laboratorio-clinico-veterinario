{{-- Componente PDF: Tabla Temporal con Gráfica --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="width: 30%;">Análisis</th>
            <th style="width: 15%;">Hora</th>
            <th style="width: 20%;">Resultado</th>
            <th style="width: 35%;">Rangos de referencia</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(is_array($fila))
            @php
                // Determinar si el resultado está fuera de rango
                $fueraDeRango = false;
                $resultadoNumerico = isset($fila['resultado']) ? floatval($fila['resultado']) : null;
                
                if ($resultadoNumerico !== null && isset($fila['rango_referencia'])) {
                    $rango = $fila['rango_referencia'];
                    preg_match('/(\d+\.?\d*)\s*-\s*(\d+\.?\d*)/', $rango, $matches);
                    
                    if (count($matches) >= 3) {
                        $min = floatval($matches[1]);
                        $max = floatval($matches[2]);
                        $fueraDeRango = ($resultadoNumerico < $min || $resultadoNumerico > $max);
                    }
                }
            @endphp
            <tr>
                <td style="font-weight: bold;">
                    {{ $fila['analisis'] ?? '' }}
                </td>
                <td style="text-align: center;">
                    {{ $fila['hora'] ?? '' }}
                </td>
                <td style="text-align: center; font-weight: bold; {{ $fueraDeRango ? 'color: #dc2626;' : '' }}">
                    {{ $fila['resultado'] ?? 'N/A' }}
                </td>
                <td style="text-align: center;">
                    {{ $fila['rango_referencia'] ?? '' }}
                    @if(!empty($fila['unidad']))
                        <span style="margin-left: 8px; color: #718096;">{{ $fila['unidad'] }}</span>
                    @endif
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>

{{-- Gráfica de líneas (solo si hay imagen capturada) --}}
@if(($componente['propiedades']['mostrar_grafica'] ?? true))
    @if(isset($chartImage) && $chartImage)
        <div style="margin-top: 15px; text-align: center;">
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
