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
                                $fueraDeRango = false;

                                if (preg_match('/(\d+(?:\.\d+)?)\s*[-–]\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    // Rango min - max (ej: "2.0 - 6.2", "100-200 ng/ml")
                                    $fueraDeRango = $resultadoNum < floatval($matches[1]) || $resultadoNum > floatval($matches[2]);
                                } elseif (preg_match('/^[<≤]\s*=\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    // <= o ≤ (ej: "<= 200 ng/ml")
                                    $fueraDeRango = $resultadoNum > floatval($matches[1]);
                                } elseif (preg_match('/^[<]\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    // < (ej: "< 21 seg")
                                    $fueraDeRango = $resultadoNum >= floatval($matches[1]);
                                } elseif (preg_match('/^[>≥]\s*=\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    // >= o ≥ (ej: ">= 10")
                                    $fueraDeRango = $resultadoNum < floatval($matches[1]);
                                } elseif (preg_match('/^[>]\s*(\d+(?:\.\d+)?)/', $rangoRef, $matches)) {
                                    // > (ej: "> 54 ug/dl")
                                    $fueraDeRango = $resultadoNum <= floatval($matches[1]);
                                }

                                if ($fueraDeRango) {
                                    $estiloExtra = 'color: #dc2626; font-weight: bold;';
                                }
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
                                    if ($rangoEncontrado && !($rangoEncontrado['es_normal'] ?? false)) {
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
