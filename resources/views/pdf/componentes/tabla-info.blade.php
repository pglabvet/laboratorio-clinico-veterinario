{{-- Componente PDF: Tabla Info (Solo lectura) --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(isset($componente['propiedades']['filas']) && is_array($componente['propiedades']['filas']))
<table>
    <tbody>
        @foreach($componente['propiedades']['filas'] as $fila)
        <tr>
            <td style="font-weight: bold; width: 40%; background-color: #f7fafc;">
                {{ is_array($fila) ? ($fila['etiqueta'] ?? '') : $fila }}
            </td>
            <td>
                {{ is_array($fila) ? ($fila['valor'] ?? '') : '' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
