{{-- Componente PDF V2: Tabla Hematológica (datos sueltos) --}}
@php
    $parametros = array_values(is_array($resultado['parametros'] ?? []) ? $resultado['parametros'] : []);
    $diferenciales = array_values(is_array($resultado['diferenciales'] ?? []) ? $resultado['diferenciales'] : []);
    $indices = array_values(is_array($resultado['indices'] ?? []) ? $resultado['indices'] : []);
    $maxRows = max(count($parametros), count($diferenciales));

    $leucocitosValor = 0;
    foreach ($parametros as $param) {
        if (str_contains(strtolower($param['nombre'] ?? ''), 'leucocito')) {
            $leucocitosValor = floatval(str_replace(',', '', $param['resultado'] ?? '0'));
            break;
        }
    }

    foreach ($diferenciales as &$dif) {
        $valorRel = floatval($dif['valor_rel'] ?? 0);
        if ($leucocitosValor > 0 && $valorRel > 0) {
            $dif['valor_abs'] = (string) round($leucocitosValor * $valorRel / 100);
        } elseif ($dif['valor_rel'] !== '' && $valorRel === 0.0) {
            $dif['valor_abs'] = '0';
        }
    }
    unset($dif);

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

    $clasificarResultado = function ($valorStr, $template, $infijo = '') {
        if ($valorStr === '' || $valorStr === null || !$template) return 'normal';
        $resultadoNum = floatval(str_replace(',', '', $valorStr));
        $tipo = $template['rango_' . $infijo . 'tipo'] ?? 'min-max';
        $min = $template['rango_' . $infijo . 'min'] ?? $template['ref_' . $infijo . 'min'] ?? null;
        $max = $template['rango_' . $infijo . 'max'] ?? $template['ref_' . $infijo . 'max'] ?? null;
        $valor = $template['rango_' . $infijo . 'valor'] ?? null;

        if ($tipo === 'min-max') {
            $minF = ($min !== null && $min !== '') ? floatval(str_replace(',', '', $min)) : null;
            $maxF = ($max !== null && $max !== '') ? floatval(str_replace(',', '', $max)) : null;
            if ($minF === null && $maxF === null) return 'normal';
            if ($minF !== null && $resultadoNum < $minF) return 'bajo';
            if ($maxF !== null && $resultadoNum > $maxF) return 'alto';
            return 'normal';
        }

        if ($valor === null || $valor === '') return 'normal';
        $valorF = floatval(str_replace(',', '', $valor));
        $fuera = match($tipo) {
            'menor' => $resultadoNum >= $valorF,
            'menor-igual' => $resultadoNum > $valorF,
            'mayor' => $resultadoNum <= $valorF,
            'mayor-igual' => $resultadoNum < $valorF,
            default => false,
        };
        if (!$fuera) return 'normal';
        return match($tipo) {
            'menor', 'menor-igual' => 'alto',
            'mayor', 'mayor-igual' => 'bajo',
            default => 'normal',
        };
    };

    $claseClasificacion = function ($clasificacion) {
        return match($clasificacion) {
            'bajo' => 'resultado-alerta',
            'alto' => 'resultado-critico',
            default => 'resultado-normal',
        };
    };
@endphp

@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($parametros) || !empty($diferenciales) || !empty($indices))
<table style="font-size: 8px; border: 1px solid #1e3a5f;">
    <thead>
        <tr>
            <th colspan="3" style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">CUADRO HEMÁTICO</th>
            <th colspan="5" style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">DIFERENCIAL LEUCOCITARIO</th>
        </tr>
        <tr>
            <th style="border: 1px solid #cbd5e0; padding: 4px;">Parámetro</th>
            <th style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">Resultado</th>
            <th style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">Ref.</th>
            <th style="border: 1px solid #cbd5e0; padding: 4px;">Tipo</th>
            <th style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">Val. Rel</th>
            <th style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">Ref.</th>
            <th style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">Val. Abs</th>
            <th style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">Ref.</th>
        </tr>
    </thead>
    <tbody>
        @for($i = 0; $i < $maxRows; $i++)
            <tr>
                @if($i < count($parametros))
                    @php 
                        $param = $parametros[$i];
                        $valorParam = $param['resultado'] ?? '';
                        $paramTemplate = null;
                        foreach ($componente['propiedades']['parametros_principales'] ?? [] as $pt) {
                            if (($pt['nombre'] ?? '') === ($param['nombre'] ?? '')) { $paramTemplate = $pt; break; }
                        }
                        $clasificacion = $clasificarResultado($valorParam, $paramTemplate);
                    @endphp
                    <td style="border: 1px solid #cbd5e0; padding: 4px;">{{ $param['nombre'] ?? '' }}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;" class="{{ $claseClasificacion($clasificacion) }}">{{ $valorParam }}</td>
                    <td style="text-align: center; color: #718096; border: 1px solid #cbd5e0; padding: 4px;">
                        {{ $paramTemplate ? $generarTextoRango($paramTemplate) : '' }}{{ !empty($param['unidad']) ? ' ' . $param['unidad'] : '' }}
                    </td>
                @else
                    <td colspan="3" style="border: 1px solid #cbd5e0;"></td>
                @endif
                
                @if($i < count($diferenciales))
                    @php 
                        $dif = $diferenciales[$i];
                        $valorRel = $dif['valor_rel'] ?? '';
                        $valorAbs = $dif['valor_abs'] ?? '';
                        $difTemplate = null;
                        foreach ($componente['propiedades']['diferenciales'] ?? [] as $dt) {
                            if (($dt['nombre'] ?? '') === ($dif['nombre'] ?? '')) { $difTemplate = $dt; break; }
                        }
                        $clasifRel = $clasificarResultado($valorRel, $difTemplate, 'rel_');
                        $clasifAbs = $clasificarResultado($valorAbs, $difTemplate, 'abs_');
                    @endphp
                    <td style="border: 1px solid #cbd5e0; padding: 4px;">{{ $dif['nombre'] ?? '' }}</td>
                    <td style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;" class="{{ $claseClasificacion($clasifRel) }}">{{ $valorRel !== '' && $valorRel !== null ? ($valorRel . ' %') : '' }}</td>
                    <td style="text-align: center; color: #718096; border: 1px solid #cbd5e0; padding: 4px;">
                        {{ $difTemplate ? $generarTextoRango($difTemplate, 'rel_') : '' }}{{ $difTemplate ? ' %' : '' }}
                    </td>
                    <td style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;">{{ $valorAbs !== '' && $valorAbs !== null ? ($valorAbs . ' mm³') : '' }}</td>
                    <td style="text-align: center; color: #718096; border: 1px solid #cbd5e0; padding: 4px;">
                        {{ $difTemplate ? $generarTextoRango($difTemplate, 'abs_') : '' }}{{ $difTemplate ? ' mm³' : '' }}
                    </td>
                @else
                    <td colspan="5" style="border: 1px solid #cbd5e0;"></td>
                @endif
            </tr>
        @endfor
        
        @if(!empty($indices))
            <tr>
                <td colspan="8" style="text-align: center; color: #1e3a5f; padding: 4px; border: 1px solid #cbd5e0; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">ÍNDICES ERITROCITARIOS</td>
            </tr>
            @foreach($indices as $indice)
            @php
                $resultado = $indice['resultado'] ?? '';
                $indiceTemplate = null;
                foreach ($componente['propiedades']['indices'] ?? [] as $it) {
                    if (($it['nombre'] ?? '') === ($indice['nombre'] ?? '')) { $indiceTemplate = $it; break; }
                }
                $clasifIndice = $clasificarResultado($resultado, $indiceTemplate);
                $textoRef = $indiceTemplate ? $generarTextoRango($indiceTemplate) : '';
                if (empty($textoRef) && $indiceTemplate) {
                    $textoRef = $indiceTemplate['referencia'] ?? '';
                }
            @endphp
            <tr>
                <td style="border: 1px solid #cbd5e0; padding: 4px;">{{ $indice['nombre'] ?? '' }}</td>
                <td style="text-align: center; border: 1px solid #cbd5e0; padding: 4px;" class="{{ $claseClasificacion($clasifIndice) }}">{{ $resultado }}</td>
                <td style="text-align: center; color: #718096; border: 1px solid #cbd5e0; padding: 4px;">
                    {{ $textoRef }}{{ !empty($indice['unidad']) ? ' ' . $indice['unidad'] : '' }}
                </td>
                <td colspan="5" style="border: 1px solid #cbd5e0;"></td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos hematológicos</p>
@endif
