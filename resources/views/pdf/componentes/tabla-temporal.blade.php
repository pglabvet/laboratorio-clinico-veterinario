{{-- Componente PDF: Tabla Temporal con Gráfica --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="width: 30%; background-color: #fef3c7;">Análisis</th>
            <th style="width: 15%; background-color: #fef3c7;">Hora</th>
            <th style="width: 20%; background-color: #d1fae5;">Resultado</th>
            <th style="width: 35%; background-color: #fef3c7;">Rangos de referencia</th>
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
                <td style="font-weight: bold; background-color: #fffbeb;">
                    {{ $fila['analisis'] ?? '' }}
                </td>
                <td style="text-align: center; background-color: #fffbeb;">
                    {{ $fila['hora'] ?? '' }}
                </td>
                <td style="text-align: center; font-weight: bold; background-color: #ecfdf5; {{ $fueraDeRango ? 'color: #dc2626;' : '' }}">
                    {{ $fila['resultado'] ?? 'N/A' }}
                    @if($fueraDeRango)
                        <span style="color: #dc2626; font-size: 12px;"> ⚠</span>
                    @endif
                </td>
                <td style="text-align: center; background-color: #fffbeb;">
                    {{ $fila['rango_referencia'] ?? '' }}
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>

{{-- Gráfica de líneas --}}
@if(($componente['propiedades']['mostrar_grafica'] ?? true))
    @if(isset($chartImage) && $chartImage)
        <div style="margin-top: 20px; page-break-inside: avoid; text-align: center;">
            <div style="font-weight: bold; font-size: 13px; margin-bottom: 10px;">
                📈 Gráfica de Resultados - {{ $componente['propiedades']['unidad_medida'] ?? 'ug/dL' }}
            </div>
            <img src="{{ $chartImage }}" style="max-width: 100%; height: auto; border: 1px solid #e5e7eb; padding: 10px; background: white;" alt="Gráfica de resultados">
        </div>
    @else
        {{-- Fallback a generación manual si no hay imagen (código anterior) --}}
        @php
            // Preparar datos para la gráfica
            $horas = [];
            $resultados = [];
            $rangosMin = [];
            $rangosMax = [];
            
            foreach($resultado as $fila) {
                if (is_array($fila) && isset($fila['resultado']) && $fila['resultado'] !== '') {
                    $horas[] = $fila['hora'] ?? '';
                    $resultados[] = floatval($fila['resultado']);
                    
                    // Extraer rangos
                    if (isset($fila['rango_referencia'])) {
                        preg_match('/(\d+\.?\d*)\s*-\s*(\d+\.?\d*)/', $fila['rango_referencia'], $matches);
                        if (count($matches) >= 3) {
                            $rangosMin[] = floatval($matches[1]);
                            $rangosMax[] = floatval($matches[2]);
                        } else {
                            $rangosMin[] = 0;
                            $rangosMax[] = 10;
                        }
                    } else {
                        $rangosMin[] = 0;
                        $rangosMax[] = 10;
                    }
                }
            }
            
            // Validar que hay datos
            if (count($resultados) > 0 && count($horas) > 0) {
                $todosLosValores = array_merge($resultados, $rangosMin, $rangosMax);
                $maxValor = max($todosLosValores);
                $minValor = min($todosLosValores);
                
                $rangoGrafica = $maxValor - $minValor;
                if ($rangoGrafica < 0.1) {
                    $rangoGrafica = max($maxValor * 0.3, 1);
                    $minValor = $maxValor - $rangoGrafica;
                }
                
                // Dimensiones del SVG
                $ancho = 450;
                $alto = 200;
                $margenIzq = 60;
                $margenSup = 20;
                $margenDer = 20;
                $margenInf = 40;
                $totalPuntos = count($horas);
                $espacioX = $totalPuntos > 1 ? $ancho / ($totalPuntos - 1) : 0;
            } else {
                $maxValor = 10;
                $minValor = 0;
                $rangoGrafica = 10;
            }
        @endphp
        
        @if(count($resultados) > 0)
        <div style="margin-top: 20px; page-break-inside: avoid;">
            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 15px;">
                <div style="font-weight: bold; font-size: 13px; margin-bottom: 10px; text-align: center;">
                    📈 Gráfica de Resultados - {{ $componente['propiedades']['unidad_medida'] ?? 'ug/dL' }}
                </div>
                
                <svg width="{{ $ancho + $margenIzq + $margenDer }}" height="{{ $alto + $margenSup + $margenInf }}" xmlns="http://www.w3.org/2000/svg">
                    {{-- Fondo --}}
                    <rect x="0" y="0" width="{{ $ancho + $margenIzq + $margenDer }}" height="{{ $alto + $margenSup + $margenInf }}" fill="white"/>
                    
                    {{-- Grid horizontal y etiquetas de valores --}}
                    @for($i = 0; $i <= 4; $i++)
                    @php
                        $y = $margenSup + ($alto * $i / 4);
                        $valor = $maxValor - ($rangoGrafica * $i / 4);
                    @endphp
                    <line x1="{{ $margenIzq }}" y1="{{ $y }}" x2="{{ $margenIzq + $ancho }}" y2="{{ $y }}" stroke="#e5e7eb" stroke-width="0.5"/>
                    <text x="{{ $margenIzq - 5 }}" y="{{ $y + 3 }}" text-anchor="end" font-size="9" fill="#666">{{ number_format($valor, 1) }}</text>
                    @endfor
                    
                    {{-- Ejes --}}
                    <line x1="{{ $margenIzq }}" y1="{{ $margenSup }}" x2="{{ $margenIzq }}" y2="{{ $margenSup + $alto }}" stroke="#374151" stroke-width="1.5"/>
                    <line x1="{{ $margenIzq }}" y1="{{ $margenSup + $alto }}" x2="{{ $margenIzq + $ancho }}" y2="{{ $margenSup + $alto }}" stroke="#374151" stroke-width="1.5"/>
                    
                    {{-- Datos y línea de resultados --}}
                    @php
                        $pathD = '';
                    @endphp
                    
                    @foreach($resultados as $i => $resultado)
                    @php
                        $x = $margenIzq + ($i * $espacioX);
                        $yNorm = ($resultado - $minValor) / max($rangoGrafica, 0.001);
                        $y = $margenSup + $alto - ($yNorm * $alto);
                        $y = max($margenSup, min($y, $margenSup + $alto));
                        
                        if ($i == 0) {
                            $pathD = "M{$x},{$y}";
                        } else {
                            $pathD .= " L{$x},{$y}";
                        }
                        
                        $esAlerta = ($resultado < $rangosMin[$i] || $resultado > $rangosMax[$i]);
                    @endphp
                    
                    {{-- Líneas de rango (pequeñas horizontales) --}}
                    @php
                        $yRangoMin = $margenSup + $alto - ((($rangosMin[$i] - $minValor) / max($rangoGrafica, 0.001)) * $alto);
                        $yRangoMax = $margenSup + $alto - ((($rangosMax[$i] - $minValor) / max($rangoGrafica, 0.001)) * $alto);
                        $yRangoMin = max($margenSup, min($yRangoMin, $margenSup + $alto));
                        $yRangoMax = max($margenSup, min($yRangoMax, $margenSup + $alto));
                    @endphp
                    
                    <line x1="{{ $x - 12 }}" y1="{{ $yRangoMin }}" x2="{{ $x + 12 }}" y2="{{ $yRangoMin }}" stroke="#ef4444" stroke-width="1" stroke-dasharray="2,1" opacity="0.5"/>
                    <line x1="{{ $x - 12 }}" y1="{{ $yRangoMax }}" x2="{{ $x + 12 }}" y2="{{ $yRangoMax }}" stroke="#ef4444" stroke-width="1" stroke-dasharray="2,1" opacity="0.5"/>
                    
                    {{-- Punto de dato --}}
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="{{ $esAlerta ? '#ef4444' : '#3b82f6' }}" stroke="white" stroke-width="1.5"/>
                    
                    {{-- Valor --}}
                    <text x="{{ $x }}" y="{{ max($y - 7, 12) }}" text-anchor="middle" font-size="8" font-weight="bold" fill="{{ $esAlerta ? '#dc2626' : '#1e40af' }}">
                        {{ number_format($resultado, 1) }}
                    </text>
                    
                    {{-- Hora --}}
                    <text x="{{ $x }}" y="{{ $margenSup + $alto + 12 }}" text-anchor="middle" font-size="8" fill="#374151">
                        {{ $horas[$i] }}
                    </text>
                    @endforeach
                    
                    {{-- Línea de resultados --}}
                    <path d="{{ $pathD }}" stroke="#3b82f6" stroke-width="2" fill="none"/>
                    
                    {{-- Leyenda --}}
                    <rect x="{{ $margenIzq }}" y="{{ $margenSup + $alto + 22 }}" width="12" height="2" fill="#3b82f6"/>
                    <text x="{{ $margenIzq + 16 }}" y="{{ $margenSup + $alto + 25 }}" font-size="7" fill="#374151">Resultado</text>
                    
                    <line x1="{{ $margenIzq + 80 }}" y1="{{ $margenSup + $alto + 23 }}" x2="{{ $margenIzq + 92 }}" y2="{{ $margenSup + $alto + 23 }}" stroke="#ef4444" stroke-width="1" stroke-dasharray="2,1"/>
                    <text x="{{ $margenIzq + 96 }}" y="{{ $margenSup + $alto + 25 }}" font-size="7" fill="#374151">Rangos</text>
                </svg>
            </div>
        </div>
        @endif
    @endif
@endif

@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
