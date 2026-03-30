{{-- Componente PDF V2: Serología (estilo datos sueltos) --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@php
    // Buscar la descripción seleccionada por el bioquímico en los datos guardados
    $descripcionPdf = '';
    
    // 1) Buscar en el resultado guardado (entrada _meta = 'descripcion')
    if (!empty($resultado) && is_array($resultado)) {
        foreach ($resultado as $fila) {
            if (is_array($fila) && ($fila['_meta'] ?? '') === 'descripcion') {
                $descripcionPdf = $fila['valor'] ?? '';
                break;
            }
        }
    }
    
    // 2) Fallback: descripción fija de la plantilla
    if (empty($descripcionPdf)) {
        $tipoDescPdf = $componente['propiedades']['tipo_descripcion'] ?? 'input';
        if ($tipoDescPdf === 'input') {
            $descripcionPdf = $componente['propiedades']['descripcion'] ?? '';
        } elseif ($tipoDescPdf === 'select') {
            // Si es seleccionable y solo hay una opción, usarla
            $opcionesPdf = array_filter(array_map('trim', explode(',', $componente['propiedades']['opciones_descripcion'] ?? '')));
            if (count($opcionesPdf) === 1) {
                $descripcionPdf = $opcionesPdf[0];
            }
        }
    }
@endphp

@if(!empty($descripcionPdf))
    <p style="font-style: italic; color: #718096; margin-bottom: 8px; font-size: 11px; text-align: center;">
        {{ $descripcionPdf }}
    </p>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            <th style="width: 60%;">PRUEBA</th>
            <th style="text-align: center;">RESULTADO</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            {{-- Saltar entradas de metadatos --}}
            @if(is_array($fila) && isset($fila['_meta']))
                @continue
            @endif
            @if(is_array($fila) && isset($fila['valor']) && $fila['valor'] !== '' && $fila['valor'] !== null)
                @php
                    $esPositivo = str_contains($fila['valor'], 'Positivo');
                @endphp
                <tr>
                    <td style="width: 60%;">
                        {{ $fila['campo'] ?? '' }}
                    </td>
                    <td style="text-align: center;" class="{{ $esPositivo ? 'resultado-critico' : 'resultado-alerta' }}">
                        {{ $fila['valor'] }}
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
