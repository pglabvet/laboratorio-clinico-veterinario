{{-- Componente PDF: Campos Etiquetados --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <tbody>
        @foreach($resultado as $campo)
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
