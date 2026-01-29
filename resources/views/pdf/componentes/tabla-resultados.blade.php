{{-- Componente PDF: Tabla de Resultados --}}
@if(isset($componente['propiedades']['titulo']))
    <div class="component-title">{{ $componente['propiedades']['titulo'] }}</div>
@endif

@if(isset($componente['propiedades']['descripcion']) && $componente['propiedades']['descripcion'])
    <p style="font-style: italic; color: #718096; margin-bottom: 8px; font-size: 9px;">
        {{ $componente['propiedades']['descripcion'] }}
    </p>
@endif

@if(!empty($resultado) && is_array($resultado))
<table>
    <thead>
        <tr>
            @foreach($componente['propiedades']['columnas'] ?? [] as $columna)
                <th>{{ $columna['nombre'] ?? '' }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($resultado as $fila)
            @if(is_array($fila))
            <tr>
                <td style="font-weight: bold; background-color: #f7fafc;">{{ $fila['nombre'] ?? '' }}</td>
                @php
                    $columnas = $componente['propiedades']['columnas'] ?? [];
                    $numColumnas = count($columnas) - 1;
                @endphp
                @for($i = 0; $i < $numColumnas; $i++)
                    <td style="text-align: center;">{{ $fila['col_' . $i] ?? '' }}</td>
                @endfor
            </tr>
            @endif
        @endforeach
    </tbody>
</table>
@else
<p style="color: #718096; font-style: italic;">Sin resultados</p>
@endif
