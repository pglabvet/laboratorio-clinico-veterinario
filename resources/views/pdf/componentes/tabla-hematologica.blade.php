{{-- Componente PDF: Tabla Hematológica --}}
@php
    // Convertir a arrays indexados si vinieron como objetos JSON (keys no secuenciales por array_filter sin array_values)
    $parametros = array_values(is_array($resultado['parametros'] ?? []) ? $resultado['parametros'] : []);
    $diferenciales = array_values(is_array($resultado['diferenciales'] ?? []) ? $resultado['diferenciales'] : []);
    $indices = array_values(is_array($resultado['indices'] ?? []) ? $resultado['indices'] : []);
    $maxRows = max(count($parametros), count($diferenciales));

    // Obtener valor de leucocitos para cálculo automático de valor absoluto
    $leucocitosValor = 0;
    foreach ($parametros as $param) {
        if (str_contains(strtolower($param['nombre'] ?? ''), 'leucocito')) {
            $leucocitosValor = floatval(str_replace(',', '', $param['resultado'] ?? '0'));
            break;
        }
    }

    // Recalcular valores absolutos: leucocitos × valor_rel / 100
    foreach ($diferenciales as &$dif) {
        $valorRel = floatval($dif['valor_rel'] ?? 0);
        if ($leucocitosValor > 0 && $valorRel > 0) {
            $dif['valor_abs'] = (string) round($leucocitosValor * $valorRel / 100);
        } elseif ($dif['valor_rel'] !== '' && $valorRel === 0.0) {
            $dif['valor_abs'] = '0';
        }
    }
    unset($dif);
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
                        $valorParam = $param['resultado'] ?? '';
                        // Buscar propiedades de plantilla por nombre
                        $paramTemplate = null;
                        foreach ($componente['propiedades']['parametros_principales'] ?? [] as $pt) {
                            if (($pt['nombre'] ?? '') === ($param['nombre'] ?? '')) { $paramTemplate = $pt; break; }
                        }
                        $refMin = $paramTemplate['ref_min'] ?? null;
                        $refMax = $paramTemplate['ref_max'] ?? null;
                        $fueraRango = false;
                        if ($valorParam !== '' && $refMin !== null && $refMax !== null) {
                            $resultadoNum = floatval(str_replace(',', '', $valorParam));
                            $refMinNum = floatval(str_replace(',', '', $refMin));
                            $refMaxNum = floatval(str_replace(',', '', $refMax));
                            $fueraRango = $resultadoNum < $refMinNum || $resultadoNum > $refMaxNum;
                        }
                    @endphp
                    <td style="font-weight: bold;">{{ $param['nombre'] ?? '' }}</td>
                    <td style="text-align: center;{{ $fueraRango ? ' color: #dc2626; font-weight: bold;' : '' }}">{{ $valorParam }}</td>
                    <td style="text-align: center;">{{ $param['unidad'] ?? '' }}</td>
                    <td style="text-align: center; color: #718096;" colspan="2">
                        {{ $paramTemplate ? ($paramTemplate['ref_min'] ?? '') . '-' . ($paramTemplate['ref_max'] ?? '') : '' }}
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
                        
                        // Buscar propiedades de plantilla por nombre
                        $difTemplate = null;
                        foreach ($componente['propiedades']['diferenciales'] ?? [] as $dt) {
                            if (($dt['nombre'] ?? '') === ($dif['nombre'] ?? '')) { $difTemplate = $dt; break; }
                        }
                        
                        // Verificar si valor relativo está fuera de rango
                        $fueraRangoRel = false;
                        if ($valorRel !== '' && $difTemplate) {
                            $refRelMin = $difTemplate['ref_rel_min'] ?? null;
                            $refRelMax = $difTemplate['ref_rel_max'] ?? null;
                            if ($refRelMin !== null && $refRelMax !== null) {
                                $valorRelNum = floatval($valorRel);
                                $fueraRangoRel = $valorRelNum < floatval($refRelMin) || $valorRelNum > floatval($refRelMax);
                            }
                        }
                        
                        // Verificar si valor absoluto está fuera de rango
                        $fueraRangoAbs = false;
                        if ($valorAbs !== '' && $difTemplate) {
                            $refAbsMin = $difTemplate['ref_abs_min'] ?? null;
                            $refAbsMax = $difTemplate['ref_abs_max'] ?? null;
                            if ($refAbsMin !== null && $refAbsMax !== null) {
                                $valorAbsNum = floatval(str_replace(',', '', $valorAbs));
                                $fueraRangoAbs = $valorAbsNum < floatval(str_replace(',', '', $refAbsMin)) || $valorAbsNum > floatval(str_replace(',', '', $refAbsMax));
                            }
                        }
                    @endphp
                    <td style="font-weight: bold;">{{ $dif['nombre'] ?? '' }}</td>
                    <td style="text-align: center;{{ $fueraRangoRel ? ' color: #dc2626; font-weight: bold;' : '' }}">{{ $valorRel !== '' && $valorRel !== null ? ($valorRel . ' %') : '' }}</td>
                    <td style="text-align: center; color: #718096;">
                        {{ $difTemplate ? ($difTemplate['ref_rel_min'] ?? '') . '-' . ($difTemplate['ref_rel_max'] ?? '') : '' }}
                    </td>
                    <td style="text-align: center;{{ $fueraRangoAbs ? ' color: #dc2626; font-weight: bold;' : '' }}">{{ $valorAbs !== '' && $valorAbs !== null ? ($valorAbs . ' mm³') : '' }}</td>
                    <td style="text-align: center; color: #718096;">
                        {{ $difTemplate ? ($difTemplate['ref_abs_min'] ?? '') . '-' . ($difTemplate['ref_abs_max'] ?? '') : '' }}
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
            @foreach($indices as $indice)
            @php
                $resultado = $indice['resultado'] ?? '';
                // Buscar propiedades de plantilla por nombre
                $indiceTemplate = null;
                foreach ($componente['propiedades']['indices'] ?? [] as $it) {
                    if (($it['nombre'] ?? '') === ($indice['nombre'] ?? '')) { $indiceTemplate = $it; break; }
                }
                $referencia = $indiceTemplate['referencia'] ?? '';
                $fueraRango = false;
                
                // Parsear el rango de referencia (formato: "vn 60-77 fl" o "8-11")
                if ($resultado !== '' && $referencia !== '') {
                    preg_match('/(-?\d+[.,]?\d*)\s*-\s*(-?\d+[.,]?\d*)/', $referencia, $matches);
                    if (count($matches) >= 3) {
                        $resultadoNum = floatval(str_replace(',', '.', str_replace('.', '', $resultado)));
                        $refMin = floatval(str_replace(',', '.', $matches[1]));
                        $refMax = floatval(str_replace(',', '.', $matches[2]));
                        $fueraRango = $resultadoNum < $refMin || $resultadoNum > $refMax;
                    }
                }
            @endphp
            <tr>
                <td colspan="2" style="font-weight: bold;">{{ $indice['nombre'] ?? '' }}</td>
                <td style="text-align: center;{{ $fueraRango ? ' color: #dc2626; font-weight: bold;' : '' }}">{{ $resultado }}</td>
                <td>{{ $indice['unidad'] ?? '' }}</td>
                <td colspan="6" style="color: #718096;">
                    {{ $referencia }}
                </td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos hematológicos</p>
@endif
