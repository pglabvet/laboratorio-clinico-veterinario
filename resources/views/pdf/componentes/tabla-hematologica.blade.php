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

    // Generar texto de rango desde datos estructurados (con fallback a campos antiguos)
    $generarTextoRango = function ($item, $infijo = '') {
        $tipo = $item['rango_' . $infijo . 'tipo'] ?? 'min-max';
        $min = $item['rango_' . $infijo . 'min'] ?? $item['ref_' . $infijo . 'min'] ?? '';
        $max = $item['rango_' . $infijo . 'max'] ?? $item['ref_' . $infijo . 'max'] ?? '';
        $valor = $item['rango_' . $infijo . 'valor'] ?? '';
        return match($tipo) {
            'min-max' => (!empty($min) || !empty($max)) ? $min . '-' . $max : '',
            'menor' => !empty($valor) ? '< ' . $valor : '',
            'menor-igual' => !empty($valor) ? '≤ ' . $valor : '',
            'mayor' => !empty($valor) ? '> ' . $valor : '',
            'mayor-igual' => !empty($valor) ? '≥ ' . $valor : '',
            default => '',
        };
    };

    // Verificar si un valor está fuera de rango según el tipo de rango estructurado
    $verificarFueraDeRango = function ($valorStr, $template, $infijo = '') {
        if ($valorStr === '' || $valorStr === null || !$template) {
            return false;
        }
        $resultadoNum = floatval(str_replace(',', '', $valorStr));
        $tipo = $template['rango_' . $infijo . 'tipo'] ?? 'min-max';
        $min = $template['rango_' . $infijo . 'min'] ?? $template['ref_' . $infijo . 'min'] ?? null;
        $max = $template['rango_' . $infijo . 'max'] ?? $template['ref_' . $infijo . 'max'] ?? null;
        $valor = $template['rango_' . $infijo . 'valor'] ?? null;
        return match($tipo) {
            'min-max' => ($min !== null && $min !== '' && $max !== null && $max !== '')
                ? ($resultadoNum < floatval(str_replace(',', '', $min)) || $resultadoNum > floatval(str_replace(',', '', $max)))
                : false,
            'menor' => ($valor !== null && $valor !== '') ? $resultadoNum >= floatval(str_replace(',', '', $valor)) : false,
            'menor-igual' => ($valor !== null && $valor !== '') ? $resultadoNum > floatval(str_replace(',', '', $valor)) : false,
            'mayor' => ($valor !== null && $valor !== '') ? $resultadoNum <= floatval(str_replace(',', '', $valor)) : false,
            'mayor-igual' => ($valor !== null && $valor !== '') ? $resultadoNum < floatval(str_replace(',', '', $valor)) : false,
            default => false,
        };
    };
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
                        $fueraRango = $verificarFueraDeRango($valorParam, $paramTemplate);
                    @endphp
                    <td style="font-weight: bold;">{{ $param['nombre'] ?? '' }}</td>
                    <td style="text-align: center;{{ $fueraRango ? ' color: #dc2626; font-weight: bold;' : '' }}">{{ $valorParam }}</td>
                    <td style="text-align: center;">{{ $param['unidad'] ?? '' }}</td>
                    <td style="text-align: center; color: #718096;" colspan="2">
                        {{ $paramTemplate ? $generarTextoRango($paramTemplate) : '' }}
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
                        
                        $fueraRangoRel = $verificarFueraDeRango($valorRel, $difTemplate, 'rel_');
                        $fueraRangoAbs = $verificarFueraDeRango($valorAbs, $difTemplate, 'abs_');
                    @endphp
                    <td style="font-weight: bold;">{{ $dif['nombre'] ?? '' }}</td>
                    <td style="text-align: center;{{ $fueraRangoRel ? ' color: #dc2626; font-weight: bold;' : '' }}">{{ $valorRel !== '' && $valorRel !== null ? ($valorRel . ' %') : '' }}</td>
                    <td style="text-align: center; color: #718096;">
                        {{ $difTemplate ? $generarTextoRango($difTemplate, 'rel_') : '' }}
                    </td>
                    <td style="text-align: center;{{ $fueraRangoAbs ? ' color: #dc2626; font-weight: bold;' : '' }}">{{ $valorAbs !== '' && $valorAbs !== null ? ($valorAbs . ' mm³') : '' }}</td>
                    <td style="text-align: center; color: #718096;">
                        {{ $difTemplate ? $generarTextoRango($difTemplate, 'abs_') : '' }}
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
                $fueraRango = $verificarFueraDeRango($resultado, $indiceTemplate);

                // Generar texto de referencia con fallback a campo antiguo
                $textoRef = $indiceTemplate ? $generarTextoRango($indiceTemplate) : '';
                if (empty($textoRef) && $indiceTemplate) {
                    $textoRef = $indiceTemplate['referencia'] ?? '';
                }
            @endphp
            <tr>
                <td colspan="2" style="font-weight: bold;">{{ $indice['nombre'] ?? '' }}</td>
                <td style="text-align: center;{{ $fueraRango ? ' color: #dc2626; font-weight: bold;' : '' }}">{{ $resultado }}</td>
                <td>{{ $indice['unidad'] ?? '' }}</td>
                <td colspan="6" style="color: #718096;">
                    {{ $textoRef }}
                </td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos hematológicos</p>
@endif
