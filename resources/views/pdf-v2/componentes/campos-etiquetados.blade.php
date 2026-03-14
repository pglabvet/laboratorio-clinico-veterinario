{{-- Componente PDF V2: Campos Etiquetados (pares etiqueta-valor) --}}
@php
    $tituloComponente = $resultado['titulo'] ?? $componente['propiedades']['titulo'] ?? null;
    $camposResultado = $resultado['campos'] ?? [];
@endphp

@if($tituloComponente)
    <div class="component-title">{{ $tituloComponente }}</div>
@endif

@php
    $col1 = $componente['propiedades']['columnas'][0]['nombre'] ?? 'Campo';
    $col2 = $componente['propiedades']['columnas'][1]['nombre'] ?? 'Resultado';
@endphp

@if(!empty($camposResultado))
<table>
    <thead>
        <tr>
            <th style="width: 40%; font-size: 10px; color: #1e3a5f;">{{ $col1 }}</th>
            <th style="width: 60%; font-size: 10px; color: #1e3a5f;">{{ $col2 }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($camposResultado as $campo)
            @if(is_array($campo) && (!empty($campo['valor']) || !empty($campo['resultado'])))
            <tr>
                <td style="width: 40%; color: #1a1a1a;">
                    {{ $campo['etiqueta'] ?? $campo['nombre'] ?? '' }}
                </td>
                <td>
                    {{ $campo['valor'] ?? $campo['resultado'] ?? '' }}
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
