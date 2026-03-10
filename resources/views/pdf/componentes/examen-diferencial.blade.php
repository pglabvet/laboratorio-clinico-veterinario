{{-- Componente PDF: Examen Diferencial --}}
@php
    $filasPlantilla = $componente['propiedades']['filas'] ?? [];

    // Generar texto de rango desde datos estructurados
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

    // Indexar filas de plantilla por nombre para búsqueda rápida
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
            @if(is_array($fila) && !empty($fila['resultado']))
            @php
                $tipoFila = $fila['tipo_fila'] ?? '3col';
                $estiloResultado = '';
                $rangoTexto = '';

                // Buscar la fila de plantilla correspondiente por nombre
                $filaTemplate = $filasPlantillaByName->get($fila['nombre'] ?? '');

                if ($filaTemplate && $tipoFila === '3col') {
                    $rangoTexto = $generarTextoRango($filaTemplate);
                    $resultadoNum = floatval($fila['resultado']);
                    $rtipo = $filaTemplate['rango_tipo'] ?? 'min-max';
                    $fueraDeRango = false;

                    if ($fila['resultado'] !== '' && is_numeric($fila['resultado'])) {
                        if ($rtipo === 'min-max') {
                            $min = is_numeric($filaTemplate['rango_min'] ?? '') ? floatval($filaTemplate['rango_min']) : null;
                            $max = is_numeric($filaTemplate['rango_max'] ?? '') ? floatval($filaTemplate['rango_max']) : null;
                            if ($min !== null && $resultadoNum < $min) { $fueraDeRango = true; }
                            if ($max !== null && $resultadoNum > $max) { $fueraDeRango = true; }
                        } elseif ($rtipo === 'menor' && is_numeric($filaTemplate['rango_valor'] ?? '')) {
                            $fueraDeRango = $resultadoNum >= floatval($filaTemplate['rango_valor']);
                        } elseif ($rtipo === 'menor-igual' && is_numeric($filaTemplate['rango_valor'] ?? '')) {
                            $fueraDeRango = $resultadoNum > floatval($filaTemplate['rango_valor']);
                        } elseif ($rtipo === 'mayor' && is_numeric($filaTemplate['rango_valor'] ?? '')) {
                            $fueraDeRango = $resultadoNum <= floatval($filaTemplate['rango_valor']);
                        } elseif ($rtipo === 'mayor-igual' && is_numeric($filaTemplate['rango_valor'] ?? '')) {
                            $fueraDeRango = $resultadoNum < floatval($filaTemplate['rango_valor']);
                        }
                    }

                    if ($fueraDeRango) {
                        $estiloResultado = 'color: #dc2626; font-weight: bold;';
                    }
                }
            @endphp
            <tr>
                <td style="font-weight: bold;">{{ $fila['nombre'] ?? '' }}</td>
                @if($tipoFila === '2col')
                    <td style="text-align: center;" {{ $tieneRangos ? 'colspan=2' : '' }}>{{ $fila['resultado'] ?? '' }}</td>
                @else
                    <td style="text-align: center; {{ $estiloResultado }}">{{ $fila['resultado'] ?? '' }}</td>
                    @if($tieneRangos)
                    <td style="text-align: center; font-size: 9px; color: #718096;">{{ $rangoTexto }}</td>
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
