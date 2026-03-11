{{-- Componente PDF: Campos Etiquetados --}}
@php
    $tituloComponente = $resultado['titulo'] ?? $componente['propiedades']['titulo'] ?? null;
    $camposResultado = $resultado['campos'] ?? [];
@endphp

@if($tituloComponente)
    <div class="component-title">{{ $tituloComponente }}</div>
@endif

@if(!empty($camposResultado))
<table>
    <tbody>
        @foreach($camposResultado as $campo)
            @if(is_array($campo) && (!empty($campo['valor']) || !empty($campo['resultado'])))
            <tr>
                <td style="font-weight: bold; width: 40%; background-color: #f7fafc;">
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
