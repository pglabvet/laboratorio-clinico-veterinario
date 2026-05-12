{{-- Componente PDF V2: Campos Etiquetados (pares etiqueta-valor) --}}
@php
    $tituloComponente = $resultado['titulo'] ?? $componente['propiedades']['titulos'][0] ?? $componente['propiedades']['titulo'] ?? null;
    $camposResultado = $resultado['campos'] ?? [];
    $camposConfig = $componente['propiedades']['campos'] ?? [];
    // Indexar config por nombre para obtener unidades
    $camposConfigByName = collect($camposConfig)->filter(fn($c) => is_array($c))->keyBy('nombre');
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
            @if(is_array($campo) && ((isset($campo['valor']) && $campo['valor'] !== '' && $campo['valor'] !== null) || (isset($campo['resultado']) && $campo['resultado'] !== '' && $campo['resultado'] !== null)))
            @php
                $nombrePDF = $campo['etiqueta'] ?? $campo['nombre'] ?? '';
                $valorPDF = $campo['valor'] ?? $campo['resultado'] ?? '';
                $unidadPDF = $campo['unidad'] ?? '';
                // Si no viene unidad en el resultado, buscar en config
                if (empty($unidadPDF)) {
                    $configPDF = $camposConfigByName->get($nombrePDF);
                    $unidadPDF = $configPDF['unidad'] ?? '';
                }
            @endphp
            <tr>
                <td style="width: 40%; color: #1a1a1a;">
                    {{ $nombrePDF }}
                </td>
                <td>
                    {{ $valorPDF }}@if(!empty($unidadPDF)) {{ $unidadPDF }}@endif
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin datos</p>
@endif
