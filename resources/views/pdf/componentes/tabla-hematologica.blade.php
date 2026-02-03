{{-- Componente PDF: Tabla Hematológica --}}
@php
    $parametros = $resultado['parametros'] ?? [];
    $diferenciales = $resultado['diferenciales'] ?? [];
    $indices = $resultado['indices'] ?? [];
    $maxRows = max(count($parametros), count($diferenciales));
@endphp

@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($parametros) || !empty($diferenciales) || !empty($indices))
<table style="font-size: 8px;">
    <thead>
        <tr>
            <th colspan="5" style="text-align: center;">CUADRO HEMÁTICO</th>
            <th colspan="5" style="text-align: center;">DIFERENCIAL LEUCOCITARIO</th>
        </tr>
        <tr>
            <th>Parámetro</th>
            <th>Resultado</th>
            <th>Unidad</th>
            <th colspan="2">Valores Ref.</th>
            <th>Tipo</th>
            <th>Valor Rel</th>
            <th>Valores Ref.</th>
            <th>Valor Abs</th>
            <th>Valores Ref.</th>
        </tr>
    </thead>
    <tbody>
        @for($i = 0; $i < $maxRows; $i++)
            <tr>
                {{-- Parámetros principales --}}
                @if($i < count($parametros))
                    @php 
                        $param = $parametros[$i];
                        $resultado = $param['resultado'] ?? '';
                        $refMin = $componente['propiedades']['parametros_principales'][$i]['ref_min'] ?? null;
                        $refMax = $componente['propiedades']['parametros_principales'][$i]['ref_max'] ?? null;
                        $fueraRango = false;
                        if ($resultado !== '' && $refMin !== null && $refMax !== null) {
                            $resultadoNum = floatval(str_replace(',', '', $resultado));
                            $refMinNum = floatval(str_replace(',', '', $refMin));
                            $refMaxNum = floatval(str_replace(',', '', $refMax));
                            $fueraRango = $resultadoNum < $refMinNum || $resultadoNum > $refMaxNum;
                        }
                    @endphp
                    <td style="font-weight: bold;">{{ $param['nombre'] ?? '' }}</td>
                    <td style="text-align: center;{{ $fueraRango ? ' color: #DC2626; font-weight: bold;' : '' }}">{{ $resultado }}</td>
                    <td style="text-align: center;">{{ $param['unidad'] ?? '' }}</td>
                    <td style="text-align: center; color: #718096;" colspan="2">
                        {{ isset($componente['propiedades']['parametros_principales'][$i]) ? 
                           ($componente['propiedades']['parametros_principales'][$i]['ref_min'] ?? '') . ' - ' . 
                           ($componente['propiedades']['parametros_principales'][$i]['ref_max'] ?? '') : '' }}
                    </td>
                @else
                    <td colspan="5"></td>
                @endif
                
                {{-- Diferenciales --}}
                @if($i < count($diferenciales))
                    @php 
                        $dif = $diferenciales[$i];
                        $valorRel = $dif['valor_rel'] ?? '';
                        $valorAbs = $dif['valor_abs'] ?? '';
                        
                        // Verificar si valor relativo está fuera de rango
                        $fueraRangoRel = false;
                        if ($valorRel !== '' && isset($componente['propiedades']['diferenciales'][$i])) {
                            $refRelMin = $componente['propiedades']['diferenciales'][$i]['ref_rel_min'] ?? null;
                            $refRelMax = $componente['propiedades']['diferenciales'][$i]['ref_rel_max'] ?? null;
                            if ($refRelMin !== null && $refRelMax !== null) {
                                $valorRelNum = floatval($valorRel);
                                $fueraRangoRel = $valorRelNum < floatval($refRelMin) || $valorRelNum > floatval($refRelMax);
                            }
                        }
                        
                        // Verificar si valor absoluto está fuera de rango
                        $fueraRangoAbs = false;
                        if ($valorAbs !== '' && isset($componente['propiedades']['diferenciales'][$i])) {
                            $refAbsMin = $componente['propiedades']['diferenciales'][$i]['ref_abs_min'] ?? null;
                            $refAbsMax = $componente['propiedades']['diferenciales'][$i]['ref_abs_max'] ?? null;
                            if ($refAbsMin !== null && $refAbsMax !== null) {
                                $valorAbsNum = floatval(str_replace(',', '', $valorAbs));
                                $fueraRangoAbs = $valorAbsNum < floatval(str_replace(',', '', $refAbsMin)) || $valorAbsNum > floatval(str_replace(',', '', $refAbsMax));
                            }
                        }
                    @endphp
                    <td style="font-weight: bold;">{{ $dif['nombre'] ?? '' }}</td>
                    <td style="text-align: center;{{ $fueraRangoRel ? ' color: #DC2626; font-weight: bold;' : '' }}">{{ $valorRel ? ($valorRel . ' %') : '' }}</td>
                    <td style="text-align: center; color: #718096;">
                        {{ isset($componente['propiedades']['diferenciales'][$i]) ? 
                           ($componente['propiedades']['diferenciales'][$i]['ref_rel_min'] ?? '') . '-' . 
                           ($componente['propiedades']['diferenciales'][$i]['ref_rel_max'] ?? '') : '' }}
                    </td>
                    <td style="text-align: center;{{ $fueraRangoAbs ? ' color: #DC2626; font-weight: bold;' : '' }}">{{ $valorAbs ? ($valorAbs . ' mm³') : '' }}</td>
                    <td style="text-align: center; color: #718096;">
                        {{ isset($componente['propiedades']['diferenciales'][$i]) ? 
                           ($componente['propiedades']['diferenciales'][$i]['ref_abs_min'] ?? '') . '-' . 
                           ($componente['propiedades']['diferenciales'][$i]['ref_abs_max'] ?? '') : '' }}
                    </td>
                @else
                    <td colspan="5"></td>
                @endif
            </tr>
        @endfor
        
        {{-- Índices Eritrocitarios --}}
        @if(!empty($indices))
            <tr>
                <td colspan="10" style="font-weight: bold; text-align: center;">ÍNDICES ERITROCITARIOS</td>
            </tr>
            @foreach($indices as $i => $indice)
            @php
                $resultado = $indice['resultado'] ?? '';
                $referencia = $componente['propiedades']['indices'][$i]['referencia'] ?? '';
                $fueraRango = false;
                
                // Parsear el rango de referencia (formato: "vn 60-77 fl" o "8-11")
                if ($resultado !== '' && $referencia !== '') {
                    preg_match('/(\d+\.?\d*)\s*-\s*(\d+\.?\d*)/', $referencia, $matches);
                    if (count($matches) >= 3) {
                        $resultadoNum = floatval($resultado);
                        $refMin = floatval($matches[1]);
                        $refMax = floatval($matches[2]);
                        $fueraRango = $resultadoNum < $refMin || $resultadoNum > $refMax;
                    }
                }
            @endphp
            <tr>
                <td colspan="2" style="font-weight: bold;">{{ $indice['nombre'] ?? '' }}</td>
                <td style="text-align: center;{{ $fueraRango ? ' color: #DC2626; font-weight: bold;' : '' }}">{{ $resultado }}</td>
                <td>{{ $indice['unidad'] ?? '' }}</td>
                <td colspan="6" style="color: #718096;">
                    {{ isset($componente['propiedades']['indices'][$i]) ? 
                       ($componente['propiedades']['indices'][$i]['referencia'] ?? '') : '' }}
                </td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos hematológicos</p>
@endif
