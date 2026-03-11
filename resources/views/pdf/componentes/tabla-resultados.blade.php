{{-- Componente PDF: Tabla de Resultados --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(isset($componente['propiedades']['descripcion']) && $componente['propiedades']['descripcion'])
    <p style="font-style: italic; color: #718096; margin-bottom: 8px; font-size: 9px;">
        {{ $componente['propiedades']['descripcion'] }}
    </p>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            @foreach($componente['propiedades']['columnas'] ?? [] as $columna)
                <th>{{ $columna['nombre'] ?? '' }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(is_array($fila))
            @php
                $valorResultado = trim($fila['col_0'] ?? '');
            @endphp
            @if($valorResultado === '' || $valorResultado === '--')
                @continue
            @endif
            <tr>
                <td style="font-weight: bold; background-color: #f7fafc;">{{ $fila['nombre'] ?? '' }}</td>
                @php
                    $columnas = $componente['propiedades']['columnas'] ?? [];
                    $numColumnas = count($columnas) - 1;
                @endphp
                @for($i = 0; $i < $numColumnas; $i++)
                    @php
                        $valor = $fila['col_' . $i] ?? '';
                        $estiloExtra = '';
                        
                        // Si es la columna de resultado (col_0) y existe rango de referencia (col_1)
                        if ($i === 0 && isset($fila['col_1'])) {
                            $rangoRef = $fila['col_1'];
                            $esMultiRango = is_string($rangoRef) && str_contains($rangoRef, "\n");
                            
                            // Solo aplicar detección fuera de rango para rangos simples (una línea)
                            if (!$esMultiRango && $valor !== '') {
                                $resultadoNum = floatval($valor);
                                $clasificacion = 'normal';

                                if (preg_match('/(\d+(?:\.\d+)?)\s*[-–]\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    $min = floatval($matches[1]);
                                    $max = floatval($matches[2]);
                                    $amplitud = $max - $min;
                                    $umbral = $amplitud * 0.15;
                                    if ($resultadoNum < $min) {
                                        $clasificacion = ($amplitud > 0 && $resultadoNum >= $min - $umbral) ? 'alerta' : 'critico';
                                    } elseif ($resultadoNum > $max) {
                                        $clasificacion = ($amplitud > 0 && $resultadoNum <= $max + $umbral) ? 'alerta' : 'critico';
                                    }
                                } elseif (preg_match('/^[<≤]\s*=\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    $val = floatval($matches[1]);
                                    $umbral = abs($val) * 0.15;
                                    if ($resultadoNum > $val) {
                                        $clasificacion = ($resultadoNum <= $val + $umbral) ? 'alerta' : 'critico';
                                    }
                                } elseif (preg_match('/^[<]\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    $val = floatval($matches[1]);
                                    $umbral = abs($val) * 0.15;
                                    if ($resultadoNum >= $val) {
                                        $clasificacion = ($resultadoNum <= $val + $umbral) ? 'alerta' : 'critico';
                                    }
                                } elseif (preg_match('/^[>≥]\s*=\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    $val = floatval($matches[1]);
                                    $umbral = abs($val) * 0.15;
                                    if ($resultadoNum < $val) {
                                        $clasificacion = ($resultadoNum >= $val - $umbral) ? 'alerta' : 'critico';
                                    }
                                } elseif (preg_match('/^[>]\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    $val = floatval($matches[1]);
                                    $umbral = abs($val) * 0.15;
                                    if ($resultadoNum <= $val) {
                                        $clasificacion = ($resultadoNum >= $val - $umbral) ? 'alerta' : 'critico';
                                    }
                                }

                                $estiloExtra = match($clasificacion) {
                                    'alerta' => 'color: #2563eb; font-weight: bold;',
                                    'critico' => 'color: #dc2626; font-weight: bold;',
                                    default => '',
                                };
                            } elseif ($esMultiRango && $valor !== '') {
                                // Buscar datos estructurados de rangos desde la plantilla
                                $templateFilas = $componente['propiedades']['filas'] ?? [];
                                $filaTemplate = null;
                                foreach ($templateFilas as $tf) {
                                    if (is_array($tf) && ($tf['nombre'] ?? '') === ($fila['nombre'] ?? '')) {
                                        $filaTemplate = $tf;
                                        break;
                                    }
                                }
                                if ($filaTemplate && !empty($filaTemplate['rangos'])) {
                                    $resultadoNum = floatval($valor);
                                    $rangoEncontrado = null;
                                    foreach ($filaTemplate['rangos'] as $rango) {
                                        $tipoR = $rango['tipo'] ?? 'min-max';
                                        $coincide = false;
                                        if ($tipoR === 'min-max') {
                                            $coincide = $resultadoNum >= floatval($rango['min'] ?? 0) && $resultadoNum <= floatval($rango['max'] ?? 0);
                                        } elseif ($tipoR === 'menor') {
                                            $coincide = $resultadoNum < floatval($rango['valor'] ?? 0);
                                        } elseif ($tipoR === 'menor-igual') {
                                            $coincide = $resultadoNum <= floatval($rango['valor'] ?? 0);
                                        } elseif ($tipoR === 'mayor') {
                                            $coincide = $resultadoNum > floatval($rango['valor'] ?? 0);
                                        } elseif ($tipoR === 'mayor-igual') {
                                            $coincide = $resultadoNum >= floatval($rango['valor'] ?? 0);
                                        }
                                        if ($coincide) {
                                            $rangoEncontrado = $rango;
                                            break;
                                        }
                                    }
                                    if ($rangoEncontrado) {
                                        if (!($rangoEncontrado['es_normal'] ?? false)) {
                                            $estiloExtra = 'color: #2563eb; font-weight: bold;';
                                        }
                                    } else {
                                        $estiloExtra = 'color: #dc2626; font-weight: bold;';
                                    }
                                }
                            }
                        }
                        
                        // Si es la columna de rango (col_1), verificar múltiples rangos
                        $esColumnaRango = ($i === 1);
                        $rangosMultiples = [];
                        if ($esColumnaRango && is_string($valor)) {
                            $rangosMultiples = array_filter(explode("\n", $valor), fn($r) => trim($r) !== '');
                        }
                        $mostrarUnidad = ($esColumnaRango && isset($fila['unidad']) && $fila['unidad'] && count($rangosMultiples) <= 1);
                    @endphp
                    <td style="text-align: center; {{ $estiloExtra }}">
                        @if($esColumnaRango && count($rangosMultiples) > 1)
                            <div style="display: inline-block; text-align: left;">
                            @foreach($rangosMultiples as $rango)
                                <div style="font-size: 8px; line-height: 1.5;">{{ trim($rango) }}</div>
                            @endforeach
                            </div>
                        @else
                            {{ $valor }}
                            @if($mostrarUnidad)
                                <span style="margin-left: 8px; color: #718096;">{{ $fila['unidad'] }}</span>
                            @endif
                        @endif
                    </td>
                @endfor
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin resultados</p>
@endif
